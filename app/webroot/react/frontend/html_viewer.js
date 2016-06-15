var Dispatcher = require('./dispatcher.js');
var HTMLViewerLoader = require('./html_viewer_loader.js');

var UI = {
    get : function(react){
        var link = react.props.link;
        var swapper = react.props.swapper;

        return (
            <div className="col">
                <iframe ref={function(ref){ react.iframe = ref; }} src={link} onLoad={react.frameCallback} />
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
        event.preventDefault();
        var link = event.target;

        while (link !== null && link.tagName !== 'A') {
             link = link.parentNode;
        }

        if(link == null){
            return false;
        }

        var hash = link.getAttribute('data-nalaid');
        this.props.swapper(hash);

        return false;
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

    // TODO: agregar estado de 'cargando' con un DIV que cubra el IFRAME
    // TODO: un link NALASRC debe recargar el React
    // TODO: un link EXTERNAL debe agregar abrirse en un nuevo TAB

});

module.exports = HTMLViewer
