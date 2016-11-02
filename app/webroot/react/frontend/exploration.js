var Dispatcher = require('./dispatcher.js');
var HTMLViewer = require('./html_viewer.js');
var HtmlUrlViewer = require('./html_url_viewer.js');
var HTMLStats = require('./html_stats.js');
var Modules = require('./modules.js');
var HttpClient = require('../components/http_client.js');
var WordCountAnalysis = require('./word_count_analysis.js');
var EmotionalAnalysis = require('./emotional_analysis.js');

var UI = {
    get : function(react){
        var target = react.props.target;
        var url = react.props.url;
        var swapper = react.swapper;
        var link = react.props.link;
        var meta = react.props.meta;
        var analysis = react.props.analysis;

        var renderUI = (
            <div id="exploration" className="module_wrapper">
                <h1 className="module_title">
                    {target.name}<br/>
                    <HtmlUrlViewer ref={function(ref){ react.urlViewer = ref; }} url={url.full_url} />
                </h1>

                <div className="col-3-4">
                    <HTMLViewer
                        link={link}
                        swapper={swapper} />
                </div>

                <div className="col-1-4">
                    <HTMLStats
                        ref={function(ref){
                            react.stats = ref;
                        }}
                        meta={meta} />

                    <WordCountAnalysis
                        ref={function(ref){
                            react.analysis.wc = ref;
                        }}
                        analysis={analysis.wc} />

                    <EmotionalAnalysis
                        ref={function(ref){
                            react.analysis.emo = ref;
                        }}
                        analysis={analysis.emo} />
                </div>
            </div>
        );

        return renderUI;
    }
};

var Exploration = React.createClass({
    request : null,
    urlViewer : null,
    stats : null,
    analysis : {},
    module : 'exploration',
    componentWillMount : function(){
        Dispatcher.configure($ReactData.config);
    },
    componentWillUnmount : function(){
        if(this.request !== null){
            this.request.abort();
        }
    },
    propTypes : {
        analysis : React.PropTypes.object.isRequired,
        link : React.PropTypes.string.isRequired,
        target : React.PropTypes.object.isRequired,
        meta : React.PropTypes.object.isRequired,
        url : React.PropTypes.object.isRequired,
    },
    swapper : function(hash){
        var params = this.getParams(hash);
        var module = this.getModule();
        var callback = this.swapperCallback.bind(this,module,params);

        Dispatcher.navigate(module,params,callback);
    },
    swapperCallback : function(module,params){
        var api = Dispatcher.resolvModuleApi(module,params);
        this.ajaxOnStart();

        if(this.request !== null){
            this.request.abort();
        }

        this.request = new HttpClient();

        this.request.getJson(api,{
            error : this.ajaxOnError.bind(this,module,params),
            done : this.ajaxOnSuccess
        });
    },
    ajaxOnStart : function(){
        this.urlViewer.loading();
        this.stats.loading();
        this.analysis.wc.loading();
        this.analysis.emo.loading();
    },
    ajaxOnError : function(module,params){
        var callback = this.swapperCallback.bind(this,module,params);

        this.urlViewer.error(callback);
        this.stats.error(callback);
    },
    ajaxOnSuccess : function(data){
        var url = data.url;
        var meta = data.meta;
        var analysis = data.analysis;

        this.urlViewer.done(url.full_url);
        this.stats.done(meta);
        this.analysis.wc.done(analysis.wc);
        this.analysis.emo.done(analysis.emo);
    },
    getParams : function(hash){
        return Modules.exploration.params(this.props.target.id,hash,this.props.target.name);
    },
    getModule : function(){
        return this.module;
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = UI.get(this);
        return renderUI;
    }
});

module.exports = Exploration
