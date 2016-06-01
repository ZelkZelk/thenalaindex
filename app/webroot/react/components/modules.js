var Modules = {
    index : {
        render : function(data,swapper){
            var TargetList = require('../components/target_list.js');
            var callback = swapper;
            var list = data;

            return (
                 <TargetList list={list} swapper={callback} />
            );
        },
        name : 'index'
    },
    histories: {
        render : function(data,swapper){
            var callback = swapper;
            var list = data;

            return (
                 <HistoryList list={list} swapper={callback} />
            );
        },
        name : 'histories'
    },
};

module.exports = Modules;
