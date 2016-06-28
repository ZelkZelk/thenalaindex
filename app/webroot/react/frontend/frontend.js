var Runner = require('../components/runner.js');
var Dispatcher = require('./dispatcher.js');
var Engine = require('./engine.js');
var Header = require('./header.js');
var Menu = require('./menu.js');

var UI = {
    header : function(engine){
        var mainUrl = $ReactData.header.mainUrl;
        var logoUrl = $ReactData.header.logoUrl;
        var swapper = engine.swapModule;
        var header = <Header
                        logoUrl={logoUrl}
                        mainUrl={mainUrl}
                        swapper={swapper} />;

        return header;
    },
    engine : function(){
        var module = $ReactData.params.module;
        var params = $ReactData.params;
        delete params.module;

        var engine = <Engine module={module} params={params} />;
        return engine;
    },
    frontend : function(){
        var renderUI = (
            <div className="wrapper">
                <div id="upper"></div>
                <div id="menu"></div>
                <div id="middle"></div>
            </div>
        );

        return renderUI;
    },
    menu : function(engine){
        var swapper = engine.swapModule;
        var menu = <Menu swapper={swapper} />
        return menu;
    }
};

Runner.start(function(){
    Dispatcher.configure($ReactData.config);

    var frontend = ReactDOM.render(
        UI.frontend(),
        document.getElementById('react-root')
    );

    var engine = ReactDOM.render(
        UI.engine(),
        document.getElementById('middle')
    );

    var header = ReactDOM.render(
        UI.header(engine),
        document.getElementById('upper')
    );

    var menu = ReactDOM.render(
        UI.menu(engine),
        document.getElementById('menu')
    );
});
