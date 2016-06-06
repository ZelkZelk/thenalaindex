var Dispatcher = require('../components/dispatcher.js');

var HistoryItem = React.createClass({
    module : 'histories',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config,$ReactData.params);
    },
    propTypes: {
        id : React.PropTypes.number.isRequired,
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
    dispatch : function(event){
        event.preventDefault();
        Dispatcher.navigate(this.getData(),this.props.swapper);
        return false;
    },
    render: function() {
        return (
            <tr>
                <td>
                    <h2>Exploracion</h2>
                    <h3>Iniciada: {this.readableDate(this.props.starting)}</h3>
                    <h3>Terminada: {this.readableDate(this.props.ending)}</h3>
                    <h3>Peticiones HTTP: {this.props.http_petitions}</h3>
                </td>
            </tr>
        );
    },
});

module.exports = HistoryItem;
