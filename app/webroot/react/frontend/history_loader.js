var UI = {
    get : function(react){
        var forwardCallback = react.dispatchForward;
        var backwardCallback = react.dispatchBackward;

        return (
            <div>
                { react.props.page < 2 ? null : <button onClick={backwardCallback}>Anterior</button> }
                { react.props.last ? null : <button onClick={forwardCallback}>Siguiente</button> }
            </div>
        )
    }
};

var HistoryLoader = React.createClass({
    module : 'histories',
    propTypes : {
        swapper : React.PropTypes.func.isRequired,
        page : React.PropTypes.number.isRequired,
        last : React.PropTypes.bool.isRequired
    },
    getInitialState : function(){
        return {
            page : this.props.page
        };
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = UI.get(this);
        return renderUI;
    },
    getModule : function(){
        return this.module;
    },
    getParams : function(page){
        var params = {
            page : this.state.page + page
        };

        return params;
    },
    dispatchBackward : function(event){
        event.preventDefault();
        this.props.swapper(this.getModule(),this.getParams(-1));
        return false;
    },
    dispatchForward : function(event){
        event.preventDefault();
        this.props.swapper(this.getModule(),this.getParams(1));
        return false;
    }
});

module.exports = HistoryLoader
