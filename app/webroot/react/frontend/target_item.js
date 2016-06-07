var Dispatcher = require('./dispatcher.js');
var Modules = require('./modules.js');

var TargetItem = React.createClass({
    module : 'histories',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config,$ReactData.params);
    },
    propTypes: {
        id : React.PropTypes.number.isRequired,
        url : React.PropTypes.string.isRequired,
        name : React.PropTypes.string.isRequired,
        first_crawl : React.PropTypes.string.isRequired,
        last_crawl : React.PropTypes.string.isRequired,
        histories : React.PropTypes.number.isRequired,
        swapper : React.PropTypes.func.isRequired
    },
    readableDate : function(rawDate){
        var components = rawDate.split(/-/);
        var year = components[0];
        var month = components[1];
        var day = components[2];

        return day + "/" + month + "/" + year;
    },
    getParams : function(){
        var id = this.props.id;
        var target = this.props.name;
        var page = 1;

        return Modules.histories.params(id,target,page);
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
    render: function() {
        return (
            <tr>
                <td>
                    <h2><a onClick={this.dispatch} href={this.resolvUrl()}>{this.props.name}</a></h2>
                    <h3>URL: <a href={this.props.url} target="_blank">{this.props.url}</a> <i>({this.props.histories} historiales)</i></h3>
                    <h3>Primera Exploracion: {this.readableDate(this.props.first_crawl)}</h3>
                    <h3>Ultima vez Explorado: {this.readableDate(this.props.last_crawl)}</h3>
                </td>
            </tr>
        );
    },
});

module.exports = TargetItem
