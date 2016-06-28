var Dispatcher = require('./dispatcher.js');

var UI = {
    get : function(react){
        var forwardCallback = react.dispatchForward;
        var backwardCallback = react.dispatchBackward;

        return (
            <div className="buttons">
                { react.props.page < 2 ? null : <a href={react.resolvBackwardUrl()} className="button" onClick={backwardCallback}>A N T E R I O R</a> }
                { react.props.last ? null : <a href={react.resolvForwardUrl()} className="button" onClick={forwardCallback}>S I G U I E N T E</a> }
            </div>
        )
    }
};

var SearchLoader = React.createClass({
    module : 'search',
    propTypes : {
        swapper : React.PropTypes.func.isRequired,
        urlResolver : React.PropTypes.func.isRequired,
        page : React.PropTypes.number.isRequired,
        last : React.PropTypes.bool.isRequired
    },
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
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
    resolvBackwardUrl : function(){
        var url = this.props.urlResolver(this.getModule(),this.getParams(-1));
        return url;
    },
    resolvForwardUrl : function(){
        var url = this.props.urlResolver(this.getModule(),this.getParams(1));
        return url;
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

module.exports = SearchLoader
