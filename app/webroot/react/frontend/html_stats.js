var Dispatcher = require('./dispatcher.js');

var States = {
    loading : 0,
    done : 1,
    error : 2
};

var UI = {
    done : function(react){
        var meta = react.state.meta;

        return (
            <div className="col">
                <div className="stat-row">
                    <div className="stat-label">Fecha Exploración</div>
                    <div className="stat-value">{react.getCreated()}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Tamaño</div>
                    <div className="stat-value">{meta.size} bytes</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">MIME</div>
                    <div className="stat-value">{meta.mime}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Checksum</div>
                    <div className="stat-value">{meta.checksum}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Hash</div>
                    <div className="stat-value">{meta.hash}</div>
                </div>
            </div>
        );
    },
    loading : function(react){
        return (
            <span>Obteniendo Metadatos de la URL...</span>
        );
    },
    error : function(react){
        var clicker = react.state.callback;

        return (
            <span>Error en la conexión <button onClick={clicker}>R E I N T E N T A R </button></span>
        );
    },
};

var HTMLStats = React.createClass({
    propTypes : {
        meta : React.PropTypes.object.isRequired
    },
    getInitialState : function(){
        return {
            meta : this.props.meta,
            state : States.done
        };
    },
    getCreated : function(){
        var created = this.props.meta.created;
        var datetime = created.split(' ');
        var date = datetime[0].split('-');

        return date[2] + '/' + date[1] + '/' + date[0];
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    done : function(meta){
        this.setState({
            meta : meta,
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
    resolvRenderUI : function(){
        var renderUI = ( <div> View not set yet! </div> );

        switch(this.state.state){
            case States.done:
                renderUI = UI.done(this);
                break;
            case States.loading:
                renderUI = UI.loading(this);
                break;
            case States.error:
                renderUI = UI.error(this);
                break;
        }

        return renderUI;
    },
});

module.exports = HTMLStats
