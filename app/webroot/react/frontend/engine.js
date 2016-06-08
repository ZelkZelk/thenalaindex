require('../node_modules/html5-history-api/history.js');

var HttpClient = require('../components/http_client.js');
var Dispatcher = require('./dispatcher.js');

var States = {
    loading : 1,
    error : 2,
    done : 3
};

var UI = {
    loading : (
        <div id="loading" className="module_wrapper">
            <h1 className="module_title">Cargando Contenido...</h1>
        </div>
    ),
    error : function(react){
        var callback = react.retry;
        var homeCallbacak = react.home;

        return (
            <div id="error" className="module_wrapper">
                <h1 className="module_title">E R R O R</h1>
                <div className="row">
                    <p>
                        Hubo un problema al cargar este contenido.
                    </p>
                </div>
                <div className="buttons">
                    <a className="button" href={react.retryUrl()} onClick={callback}>R E I N T E N T A R</a>
                    <a className="button" href={react.homeUrl()} onClick={homeCallbacak}>H O M E</a>
                </div>
            </div>
        );
    },
    done : function(data,swapper){
        return Dispatcher.resolvModuleUI(data, swapper);
    }
};

var Engine = React.createClass({
    request : null,
    propTypes: {
        module : React.PropTypes.string.isRequired,
        params : React.PropTypes.object.isRequired
    },
    getInitialState : function(){
        var state = this.getModuleState(this.props.module,this.props.params);
        state.state = States.loading;
        return state;
    },
    getModuleState : function(module,params){
        var state = {
            module : module,
            params : params
        };

        return state;
    },
    componentWillMount : function(){
        this.fetch();
        this.historyCallbacks();
    },
    historyCallbacks : function(){
        var self = this;

        window.addEventListener("popstate", function(event) {
            var module;
            var params;

            if(history.state === null){
                return;
            }

            if(typeof history.state.module === 'undefined'){
                return;
            }

            if(typeof history.state.params === 'undefined'){
                return;
            }

            module = history.state.module;
            params = history.state.params;

            self.swapModule(module,params);
        });

        history.pushState(this.getInitialState(),null,location.href);
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
    swapModule : function(module,params){
        this.load(module,params);
    },
    getModule : function(){
        var module = this.state.module;
        return module;
    },
    timeout : null,
    fetch : function(){
        this.fetchModule(this.state,this.error,this.done);
    },
    fetchModule : function(state,errorCallback,doneCallback){
        var module = state.module;
        var params = state.params;
        var api = this.resolvApi(module,params);

        if(this.request !== null){
            this.request.abort();
        }

        this.request = new HttpClient();

        this.request.getJson(api,{
            error : errorCallback,
            done : doneCallback
        });
    },
    resolvApi : function(module,params){
        var api = Dispatcher.resolvModuleApi(module,params);
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
        var renderUI = UI.error(this);
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
    home : function(event){
        event.preventDefault();
        Dispatcher.navigate('index',{},this.swapModule);
        return false;
    },
    homeUrl : function(){
        var url = Dispatcher.resolvModuleUrl('index',{});
        return url;
    },
    retryUrl : function(){
        var url = Dispatcher.resolvModuleUrl(history.state.module,history.state.params);
        return url;
    },
    retry : function(event){
        event.preventDefault();
        this.load(history.state.module,history.state.params);
        return false;
    },
    load : function(module,params){
        Dispatcher.configure($ReactData.config);
        var state = this.getModuleState(module,params);
        state.state = States.loading;
        this.setState(state, this.fetch);
    }
});

module.exports = Engine
