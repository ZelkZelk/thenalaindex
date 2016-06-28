var Dispatcher = require('./dispatcher.js');
var Modules = require('./modules.js');

var SearchItem = React.createClass({
    module : 'exploration',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    propTypes: {
        item : React.PropTypes.object.isRequired,
        swapper : React.PropTypes.func.isRequired
    },
    resolvCreated : function(){
        var rawDate = this.props.item.created;
        var components = rawDate.split(/\s/);
        var date = components[0].split(/-/);
        var year = date[0];
        var month = date[1];
        var day = date[2];

        return day + "/" + month + "/" + year;
    },
    getParams : function(){
        var id = this.props.item.id;
        var target = this.props.item.full_url;
        var page = 1;

        return {
            id : this.props.item.target_id,
            target : this.props.item.target,
            hash : this.trim(this.props.item.hash)
        };
    },
    resolvUrl : function(){
        var url = Dispatcher.resolvModuleUrl(this.module,this.getParams());
        return url;
    },
    dispatch : function(event){
        event.preventDefault();
        Dispatcher.navigate(this.module,this.getParams(),this.props.swapper);
        return false;
    },
    resolvURL : function(){
        var url = this.props.item.full_url;
        return url;
    },
    resolvTitle : function(){
        var h1 = this.trim(this.props.item.h1);

        if(h1 !== ''){
            return h1;
        }

        var title = this.trim(this.props.item.title);
        return title;
    },
    trim : function(str){
        return str.replace(/^\s+|\s+$/g,'');
    },
    render: function() {
        return (
            <li onClick={this.dispatch}>
                <a href={this.resolvUrl()}>
                    <h2>{this.resolvTitle()}</h2>
                    <div><b>URL:</b> {this.resolvURL()}</div>
                    <div><b>Explorado:</b> {this.resolvCreated()}</div>
                </a>
            </li>
        );
    },
});

module.exports = SearchItem
