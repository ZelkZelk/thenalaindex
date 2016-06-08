var Dispatcher = require('./dispatcher.js');

var HistoryItem = React.createClass({
    module : 'histories',
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
        img_crawled : React.PropTypes.number.isRequired
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
    getData : function(){
        return {
            id : this.props.id,
            module : this.module,
            target : this.props.name
        };
    },
    render: function() {
        return (
            <li>
                <a>
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
