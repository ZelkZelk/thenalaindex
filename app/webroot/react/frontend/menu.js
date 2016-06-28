var Dispatcher = require('./dispatcher.js');
var Searcher = require('./searcher.js');

var UI = {
    get : function(react){
        var swapper = react.swapper;

        var renderUI = (
            <ul className="menu">
                <li><Searcher swapper={swapper} /></li>
            </ul>
        );

        return renderUI;
    }
};

var Menu = React.createClass({
    propTypes : {
        swapper : React.PropTypes.func.isRequired
    },
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    swapper : function(module,params){
        Dispatcher.navigate(module,params,this.props.swapper);
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = UI.get(this);
        return renderUI;
    },
});

module.exports = Menu
