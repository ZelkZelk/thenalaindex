var Runner = require('../components/runner.js');
var Dispatcher = require('../components/dispatcher.js');
var HttpClient = require('../components/http_client.js');

var States = {
    loading : 1,
    error : 2,
    done : 3
};

var UI = {
    loading : (
        <div>C A R G A N D O</div>
    ),
    error : function(retry){
        var callback = retry;

        return (
            <div>
                <p>E R R O R</p>
                <button onClick={callback}>REINTENTAR</button>
            </div>
        );
    },
    done : function(data,swapper){
        return Dispatcher.resolvModuleUI(data,swapper);
    }
};

var Frontend = React.createClass({
    request : null,
    propTypes: {
        module : React.PropTypes.string.isRequired
    },
    getInitialState : function(){
        var state = this.getModuleState(this.props.module);
        state.state = States.loading;

        return state;
    },
    getModuleState : function(module){
        var state = {
            module : module
        };

        var querystring = Dispatcher.resolvQueryStringData(module);

        if(querystring !== false){
            state.querystring = querystring;
        }

        return state;
    },
    componentWillMount : function(){
        this.fetch();
        this.historyCallbacks();
    },
    historyCallbacks : function(){
        window.addEventListener("popstate", function(e) {
            console.debug(event)
            console.debug(location.href)
        });
    },
    getCurrentState : function(){
        var state = null;

        if(typeof this.state !== 'undefined'){
            if(typeof this.state.state !== 'undefined'){
                state = this.state;
            }
        }

        if(state === null){
            state = this.getInitialState();
        }

        return state.state;
    },
    swapModule : function(data){
        var module = data.module;

        this.load(data);
    },
    getModule : function(){
        var module = this.state.module;
        return module;
    },
    fetch : function(){
        this.fetchModule(this.state);
    },
    fetchModule : function(data){
        var api = this.resolvApi(data);
        var request = new HttpClient();

        request.getJson(api,{
            error : this.error,
            done : this.done
        });

        this.request = request;
    },
    resolvApi : function(data){
        var api = Dispatcher.resolvModuleApi(data);
        return api;
    },
    resolvRenderUI : function(state,module){
        var renderUI = ( <div>No View Set... yet! </div> );

        switch(state){
            case States.loading:
                renderUI = this.resolvLoadingUI();
                break;
            case States.error:
                renderUI = this.resolvErrorUI();
                break;
            case States.done:
                renderUI = this.resolvDoneUI(module);
                break;
        }

        return renderUI;
    },
    resolvLoadingUI : function(){
        var render = UI.loading;
        return render;
    },
    resolvErrorUI : function(){
        var renderUI = UI.error(this.retry);
        return renderUI;
    },
    resolvDoneUI : function(module){
        var data = this.state.data;
        data.module = module;

        var renderUI = UI.done(data,this.swapModule);
        return renderUI;
    },
    render : function(){
        var state = this.getCurrentState();
        var module = this.getModule();
        var renderUI = this.resolvRenderUI(state,module);

        return renderUI;
    },
    done : function(data){
        this.setState({
            state : States.done,
            data : data
        });
    },
    error : function(xhr,textStatus,error){
        this.setState({
            state : States.error
        });
    },
    retry : function(event){
        this.load();
    },
    load : function(data){
        Dispatcher.configure($ReactData.config,data);
        var state = this.getModuleState(data.module);
        state.state = States.loading;
        this.setState(state, this.fetch);
    }
});

Runner.start(function(){
    Dispatcher.configure($ReactData.config,$ReactData.params);
    var module = Dispatcher.resolv();

    var frontend = ReactDOM.render(
        <Frontend module={module} />,
        document.getElementById('react-root')
    );
});
