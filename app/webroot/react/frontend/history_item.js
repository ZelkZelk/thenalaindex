var Dispatcher = require('./dispatcher.js');

var HistoryItem = React.createClass({
    module : 'exploration',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    propTypes: {
        id : React.PropTypes.number.isRequired,
        index : React.PropTypes.number.isRequired,
        starting : React.PropTypes.string.isRequired,
        ending : React.PropTypes.string.isRequired,
        http_petitions : React.PropTypes.number.isRequired,
        css_crawled : React.PropTypes.number.isRequired,
        html_crawled : React.PropTypes.number.isRequired,
        js_crawled : React.PropTypes.number.isRequired,
        img_crawled : React.PropTypes.number.isRequired,
        hash : React.PropTypes.string.isRequired,
        target : React.PropTypes.string.isRequired,
        swapper : React.PropTypes.func.isRequired,
    },
    readableDate : function(rawDate){
        var components = rawDate.split(/ /);
        var date = components[0];
        var time = components[1];

        var dataComponents = date.split(/-/);
        var year = dataComponents[0];
        var month = dataComponents[1];
        var day = dataComponents[2];

        return day + "/" + month + "/" + year + " " + time;
    },
    getParams : function(){
        return {
            id : this.props.id,
            hash : this.props.hash,
            target : this.props.target
        };
    },
    getModule : function(){
        return this.module;
    },
    resolvUrl : function(){
        var url = Dispatcher.resolvModuleUrl(this.getModule(),this.getParams());
        return url;
    },
    dispatch : function(event){
        event.preventDefault();
        Dispatcher.navigate(this.getModule(),this.getParams(),this.props.swapper);
        return false;
    },
    render: function() {
        return (
            <li>
                <a href={this.resolvUrl()} onClick={this.dispatch}>
                    <h2>Exploracion #{this.props.index}</h2>
                    <div><b>Iniciada:</b> {this.readableDate(this.props.starting)}</div>
                    <div><b>Terminada:</b> {this.readableDate(this.props.ending)}</div>
                    <div><b>Peticiones HTTP:</b> {this.props.http_petitions}</div>
                </a>
            </li>
        );
    },
});

module.exports = HistoryItem;
