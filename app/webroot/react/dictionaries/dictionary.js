var Runner = require('../components/runner.js');
var HttpClient = require('../components/http_client.js');

var UI = {
    dictionary : function(data){
        return (
            <div className="dictionary">
                <Dictionary api={data.api.url} />
            </div>
        );
    },
    pull : function(react){
        return (
            <div>
                <i className="fa fa-spinner fa-spin fa-fw"></i> Conectando...
            </div>
        );
    },
    push : function(react){
        return (
            <div>push</div>
        );
    },
    pullError : function(react){
        return (
            <div>pull error</div>
        );
    },
    pushError : function(react){
        return (
            <div>push error</div>
        );
    },
    empty : function(react){
        return (
            <div>empty</div>
        );
    },
    ready : function(react){
        var data = react.state.data;

        var buttons = Object.keys(data.options).map(function(value){
            var label = data.options[value];
            return (
                <button onClick={react.push.bind(data.id, value)} key={value} value={value}>{label}</button> );
        });

        return (
            <div>
                <div><b>Palabra:</b> {data.word}</div>
                <div><b>Referencia:</b> <a href={data.reference} target="_blank">click aqui</a></div>
                {buttons}
            </div>
        );
    },
};

var States = {
    pull : 0,
    push : 1,
    ready : 2,
    pullError : 3,
    pushError : 4,
    empty : 5
};

var Dictionary = React.createClass({
    propTypes : {
        api : React.PropTypes.string.isRequired,
    },
    getInitialState : function(){
        return {
            state : States.pull
        };
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    componentWillMount : function(){
        this.pullRequest();
    },
    request : null,
    pull : function(){
        this.setState({
            state : States.pull
        }, this.pullRequest);
    },
    pullRequest : function(){
        if(this.request !== null){
            this.request.abort();
        }

        this.request = new HttpClient();

        this.request.getJson(this.props.api,{
            error : this.pullError,
            done : this.pullSuccess
        });
    },
    pullSuccess : function(data){
        if(data.id !== false){
            this.ready(data);
        }
        else{
            this.empty();
        }
    },
    pullError : function(){
        this.setState({
            state : States.pullError
        });
    },
    ready : function(data){
        this.setState({
            state : States.ready,
            data : data
        });
    },
    empty : function(){
        this.setState({
            state : States.empty
        });
    },
    resolvRenderUI : function(){
        var renderUI = ( <span>View not set yet! </span> );

        switch(this.state.state){
            case States.pull:
                renderUI = UI.pull(this);
                break;
            case States.push:
                renderUI = UI.push(this);
                break;
            case States.ready:
                renderUI = UI.ready(this);
                break;
            case States.pushError:
                renderUI = UI.pushError(this);
                break;
            case States.pullError:
                renderUI = UI.pullError(this);
                break;
            case States.empty:
                renderUI = UI.empty(this);
                break;
        }

        return renderUI;
    },
});


Runner.start(function(){
    var frontend = ReactDOM.render(
        UI.dictionary($ReactData),
        document.getElementById('react-root')
    );
});
