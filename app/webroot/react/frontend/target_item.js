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
        var historial;

        if(this.props.histories > 1){
            historial = 'historiales';
        }
        else{
            historial = 'historial';
        }

        return (
            <li onClick={this.dispatch}>
                <a href={this.resolvUrl()}>
                    <h2>{this.props.name}</h2>
                    <div><b>URL:</b> {this.props.url} <i>({this.props.histories} {historial})</i></div>
                    <div><b>Primera Exploracion:</b> {this.readableDate(this.props.first_crawl)}</div>
                    <div><b>Ultima vez Explorado:</b> {this.readableDate(this.props.last_crawl)}</div>
                </a>
            </li>
        );
    },
});

module.exports = TargetItem
