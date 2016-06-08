var Dispatcher = require('./dispatcher.js');
var Links = require('../components/links.js');

var UI = {
    get : function(react){
        var properties = react.props;
        var mainClicker = react.mainClicker;

        var renderUI = (
            <div className="header">
                <span>
                    <a onFocus={Links.blurMe} href={properties.mainUrl} onClick={mainClicker}>
                        <img src={properties.logoUrl} /> <br />
                        The Nala Index
                    </a>
                </span>
            </div>
        );

        return renderUI;
    }
};

var Header = React.createClass({
    propTypes : {
        mainUrl : React.PropTypes.string.isRequired,
        logoUrl : React.PropTypes.string.isRequired,
        swapper : React.PropTypes.func.isRequired
    },
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    mainClicker : function(event){
        event.preventDefault();
        Dispatcher.navigate('index',{},this.props.swapper);
        return false;
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

module.exports = Header
