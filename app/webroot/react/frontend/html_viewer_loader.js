var Dispatcher = require('./dispatcher.js');

var States = {
    loading : 0,
    loaded : 1
};

var UI = {
    loading : function(react){
        return (
            <div id="html_viewer_loading" className='loading' ref={function(ref){ react.loader = ref; }}>
                <div className="message">Recuperando página indexada...</div>
            </div>
        );
    },
    loaded : function(react){
        return (
            <span>&nbsp;</span>
        );
    },
};

var HTMLViewerLoader = React.createClass({
    propTypes : {

    },
    getInitialState : function(){
        return {
            state : States.loading
        };
    },
    componentDidMount: function() {
        var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        var loading = document.getElementById('html_viewer_loading');
        loading.style.height = h + 'px';
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = ( <div> View not set Yet! </div> );

        switch (this.state.state) {
            case States.loading:
                renderUI = UI.loading(this);
                break;
            case States.loaded:
                renderUI = UI.loaded(this);
                break;
        }

        return renderUI;
    },
    loaded : function(){
        this.setState({
            state : States.loaded
        });
    }
});

module.exports = HTMLViewerLoader
