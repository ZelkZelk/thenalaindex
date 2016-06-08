var HttpClient = require('../components/http_client.js');

var States = {
    idling : 0,
    loading : 1,
    void : 2
};

var UI = {
    idling : function(handler){
        var callback = handler;

        return (
            <button onClick={callback}>Ver Mas Sitios</button>
        );
    },
    loading : (
        <div>C A R G A N D O</div>
    ),
    void : (
        <div>&nbsp;</div>
    )
};

var TargetLoader = React.createClass({
    request : null,
    propTypes: {
        older : React.PropTypes.number.isRequired,
        refresh_handler : React.PropTypes.func.isRequired,
        config : React.PropTypes.object.isRequired
    },
    getInitialState : function(){
        return {
            state : States.idling
        };
    },
    load : function(){
        this.setState({
            state : States.loading
        });

        this.fetch();
    },
    resolvRenderUI : function(){
        var renderUI = '<div>View not set yet</div>';

        switch(this.state.state){
            case States.idling:
                renderUI = UI.idling(this.load);
                break;
            case States.loading:
                renderUI = UI.loading;
                break;
            case States.void:
                renderUI = UI.void;
                break;
        }

        return renderUI;
    },
    resolvUrl : function(){
        var url = this.props.config.url.index + "?older=" + this.props.older;
        return url;
    },
    fetch : function(older){
        var url = this.resolvUrl();
        var request = new HttpClient();

        request.getJson(url,{
            error : this.error,
            done : this.done
        });

        this.request = request;
    },
    idling : function(){
        this.setState({
            state : States.idling
        });
    },
    error : function(xhr,statusText,error){
        // TODO: error message
        this.idling();
    },
    done : function(data){
        if(data.length === 0){
            this.empty();
        }
        else{
            this.idling();
        }
    },
    empty : function(){
        // TODO: empty message

        this.setState({
            state : States.void
        });
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
});

module.exports = TargetLoader
