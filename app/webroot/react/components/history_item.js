var Dispatcher = require('../components/dispatcher.js');

var HistoryItem = React.createClass({
    module : 'histories',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config,$ReactData.params);
    },
    propTypes: {
        id : React.PropTypes.number.isRequired,
        url : React.PropTypes.string.isRequired,
    },
    readableDate : function(rawDate){
        var components = rawDate.split(/-/);
        var year = components[0];
        var month = components[1];
        var day = components[2];

        return day + "/" + month + "/" + year;
    },
    getData : function(){
        return {
            id : this.props.id,
            module : this.module,
            target : this.props.name
        };
    },
    resolvUrl : function(){
        var url = Dispatcher.resolvModuleUrl(this.getData());
        return url;
    },
    dispatch : function(event){
        event.preventDefault();
        Dispatcher.navigate(this.getData(),this.props.swapper);
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
