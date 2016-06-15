var Dispatcher = require('./dispatcher.js');
var HTMLViewerLoader = require('./html_viewer_loader.js');

var UI = {
    get : function(react){
        var link = react.props.link;
        var swapper = react.props.swapper;

        return (
            <div className="col">
                <iframe ref={function(ref){ react.iframe = ref; }} onLoad={react.frameCallback} />
                <HTMLViewerLoader ref={function(ref){ react.loader = ref; }} />
            </div>
        );
    },
};

var HTMLViewer = React.createClass({
    iframe : null,
    loader : null,
    module : 'exploration',
    propTypes : {
        link : React.PropTypes.string.isRequired,
        swapper : React.PropTypes.func.isRequired,
    },
    componentDidMount: function() {
        var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        this.iframe.style.height = h + 'px';
        this.frameRelocation(this.props.link);
    },
    frameRelocation : function(url){
        var iframe = this.iframe;
        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

        iframeDoc.location.replace(url);
    },
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = UI.get(this);
        return renderUI;
    },
    frameCallback : function(event){
        var iframe = event.target;
        var document = iframe.contentDocument || iframe.contentWindow.document;
        var links = document.getElementsByTagName('A');

        for(var i in links){
            var link = links[i];

            if(typeof link.getAttribute !== 'undefined'){
                var href = link.getAttribute('href');

                if(this.isNala(link)){
                    link.onclick = this.linkCallback;
                }
                else{
                    link.setAttribute('target','_blank');
                }
            }
        }

        iframe.style.display = 'block';
        this.loader.loaded();
    },
    getModule : function(){
        return this.module;
    },
    linkCallback : function(event){
        var link = event.target;

        while (link !== null && link.tagName !== 'A') {
             link = link.parentNode;
        }

        if(link !== null){
            var hash = link.getAttribute('data-nalaid');

            if(this.isSwappable(link)){
                var url = link.getAttribute('href');
                this.frameRelocation(url);
                this.props.swapper(hash);
                
                event.preventDefault();
                return false;

            }
        }

        return true;
    },
    isSwappable : function(link){
        var html = link.getAttribute('data-ishtml');

        if(typeof html === 'undefined'){
            return false;
        }

        return html;
    },
    isNala : function(link){
        var hash = link.getAttribute('data-nalaid');

        if(typeof hash === 'undefined'){
            return false;
        }

        if(hash === null){
            return false;
        }

        if(hash === ''){
            return false;
        }

        return true;
    }
});

module.exports = HTMLViewer
