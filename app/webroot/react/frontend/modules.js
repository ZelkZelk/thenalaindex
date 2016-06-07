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
};

module.exports = Modules;
