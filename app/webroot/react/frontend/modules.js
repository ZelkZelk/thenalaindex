var Modules = {
    index : {
        render : function(data,swapper){
            var TargetList = require('./target_list.js');
            var list = data;

            return (
                 <TargetList list={list} swapper={swapper} />
            );
        },
        params : {

        },
        name : 'index'
    },
    histories: {
        render : function(data,swapper){
            var HistoryList = require('./history_list.js');
            var blob = data;

            return (
                 <HistoryList
                    list={blob.histories}
                    target={blob.target}
                    page={blob.page}
                    swapper={swapper} />
            );
        },
        params : function(id,target,page){
            return {
                id : id,
                target : target,
                page : page
            };
        },
        name : 'histories'
    },
    exploration : {
        render : function(data,swapper){
            var Exploration = require('./exploration.js');
            var analysis = data.analysis;
            var link = data.link;
            var target = data.target;
            var meta = data.meta;
            var url = data.url;

            return (
                 <Exploration
                    analysis={analysis}
                    link={link}
                    target={target}
                    meta={meta}
                    url={url}/>
            );
        },
        params : function(id,hash,target){
            return {
                id : id,
                target : target,
                hash : hash
            };
        },
        name : 'exploration'
    },
    search : {
        render : function(data,swapper){
            var Search = require('./search.js');
            var results = data.results;
            var term = data.term;
            var page = data.page;

            return (
                 <Search
                    page={page}
                    swapper={swapper}
                    term={term}
                    results={results}/>
            );
        },
        params : function(q){
            return {
                term :q,
            };
        },
        name : 'search'
    },
};

module.exports = Modules;
