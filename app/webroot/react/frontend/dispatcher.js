require('../node_modules/html5-history-api/history.js');

var Modules = require('./modules.js');

var Dispatcher = {
    config : null,
    configure : function(config){
        this.config = config;
    },
    navigate : function(module,params,sweeper){
        var data = {
            module : module,
            params : params
        };

        var url = this.resolvModuleUrl(module,params);
        history.pushState(data,null,url);
        sweeper(module,params);
    },
    getSlug : function(string){
        var slug = string
                        .toLowerCase()
                        .replace(/ /g,'-')
                        .replace(/[^áéíóúÁÉÍÓÚÑñ\w-]+/g,'');

        return slug;
    },
    resolvModuleUI : function(data,swapper){
        var render = ( <div>No View Set.. yet!</div> );
        var module = data.module;

        if(typeof Modules[module] === 'undefined'){
            return render;
        }

        render = Modules[module].render(data,swapper);
        return render;
    },
    resolvModuleApi : function(module,params){
        var api = '/api-config-not-set-yet.json'

        if(typeof this.config === 'undefined'){
            return api;
        }

        if(typeof this.config.api === 'undefined'){
            return api;
        }

        if(typeof this.config.api[module] === undefined){
            return api;
        }

        api = this.config.api[module];
        api = api + this.resolvModuleQueryString(module,params);
        return api;
    },
    resolvModuleUrl : function(module,params){
        var url = '/url-config-not-set-yet.html'

        if(typeof this.config === 'undefined'){
            return url;
        }

        if(typeof this.config.url === 'undefined'){
            return url;
        }

        if(typeof this.config.url[module] === undefined){
            return url;
        }

        url = this.getUrlReplacement(this.config.url[module],params);
        return url;
    },
    resolvModuleQueryString : function(module,params){
        var querystringdata = this.resolvQueryStringData(module,params);

        if(querystringdata === false){
            return false;
        }

        var querystring = '?';

        for(var param in querystringdata){
            var value = querystringdata[param];
            querystring += param + "=" + value + "&";
        }

        return querystring.substring(0, querystring.length - 1);
    },
    resolvQueryStringData : function(module,params){
        var querystring = false;

        if(typeof this.config === 'undefined'){
            return querystring;
        }

        if(typeof this.config.querystring === 'undefined'){
            return querystring;
        }

        if(typeof this.config.querystring[module] === undefined){
            return querystring;
        }

        var querystring = {};

        for(var name in this.config.querystring[module]){
            var param = this.config.querystring[module][name];

            if(typeof params[param] !== 'undefined'){
                querystring[name] = params[param];
            }
        }

        return querystring;
    },
    getUrlReplacement : function(rawUrl,params){
         var res = rawUrl.match(/(%.*?%)/g);
         var url = rawUrl;

         for(var i in res){
             var token = res[i].replace(/%/g,'');
             var replacement = this.getPathReplacement(token,params);
             var needle = "%" + token + "%";

             url = url.replace(needle,replacement);
         }

         return url;
    },
    getPathReplacement : function(token,params){
        var path = 'path-not-set-yet';

        if(typeof params[token] !== 'undefined'){
            path = this.getSlug(String(params[token]));
        }

        return path;
    }
};

module.exports = Dispatcher;
