var LinkList = require('./link_list.js');
var Runner = require('../components/runner.js');
var SingleAbmTable = require('./single_abm_table.js');

var NotificationsEmail = {
    onLinkClick : function(data,e){
        e.preventDefault();

        var title = <span>{data.label} <small>{data.sublabel}</small></span>

        var table = ReactDOM.render(
            <SingleAbmTable
                env={data}
                field={$ReactData.abm.field}
                fieldLabel={$ReactData.abm.fieldLabel}
                fieldIcon={$ReactData.abm.fieldIcon}
                feedApi={$ReactData.abm.feedApi}
                pushApi={$ReactData.abm.pushApi}
                dropApi={$ReactData.abm.dropApi}
                editApi={$ReactData.abm.editApi}
                icon={data.icon}
                title={title}
                emptyText={$ReactData.abm.emptyText}/>,
            document.getElementById('table')
        );

        table.start();
    }
};

Runner.start(function(){
    for(var i in $ReactData.links){
        $ReactData.links[i].click = NotificationsEmail.onLinkClick;
    }

    ReactDOM.render(
        <div>
            <LinkList links={$ReactData.links} />
            <div id="table"/>
        </div>,
        document.getElementById('react-root')
    );
});
