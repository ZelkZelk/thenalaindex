var Modules = {
    index : {
        render : function(data,callbacks){
            var TargetList = require('../components/target_list.js');
            var swapper = callbacks.swapper;
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
        render : function(data,callbacks){
            var HistoryList = require('../components/history_list.js');
            var swapper = callbacks.swapper;
            var feeder = callbacks.feeder;
            var blob = data;

            return (
                 <HistoryList
                    list={blob.histories}
                    target={blob.target}
                    page={blob.page}
                    feeder={feeder}
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
