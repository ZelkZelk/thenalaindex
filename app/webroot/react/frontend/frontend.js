require('../node_modules/html5-history-api/history.js');

var Runner = require('../components/runner.js');
var Dispatcher = require('./dispatcher.js');
var Engine = require('./engine.js');

var UI = {
    frontend : function(module,params){
        var renderUI = (
            <Engine module={module} params={params} />
        );

        return renderUI;
    }
};

Runner.start(function(){
    Dispatcher.configure($ReactData.config);
    var module = $ReactData.params.module;
    var params = $ReactData.params;
    delete params.module;

    ReactDOM.render(
        UI.frontend(module,params),
        document.getElementById('react-root')
    );
});
