var States = {
    loading : 0,
    done : 1,
    error : 2
};

var UI = {
    done : function(react){
        var url = react.state.url;

        return (
            <a href={url} target="_blank">{url}</a>
        );
    },
    loading : function(react){
        return (
            <span className="subtitle">Obteniendo URL original...</span>
        );
    },
    error : function(react){
        var clicker = react.state.callback;

        return (
            <span className="subtitle">Error en la conexión</span>
        );
    },
};

var HtmlUrlViewer = React.createClass({
    propTypes : {
        url : React.PropTypes.string.isRequired
    },
    getInitialState : function(){
        return {
            url : this.props.url,
            state : States.done
        };
    },
    done : function(url){
        this.setState({
            url : url,
            state : States.done
        });
    },
    loading : function(){
        this.setState({
            state : States.loading
        });
    },
    error : function(callback){
        this.setState({
            state : States.error,
            callback : callback
        });
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = ( <span>View not set yet! </span> );

        switch(this.state.state){
            case States.loading:
                renderUI = UI.loading(this);
                break;
            case States.done:
                renderUI = UI.done(this);
                break;
            case States.error:
                renderUI = UI.error(this);
                break;
        }

        return renderUI;
    },
});

module.exports = HtmlUrlViewer
