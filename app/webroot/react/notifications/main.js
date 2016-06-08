(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var Runner = {
    run: function (callback) {
        callback();
    },
    start: function (callback) {
        const loadedStates = ['complete', 'loaded', 'interactive'];

        if (loadedStates.includes(document.readyState) && document.body) {
            callback();
        } else {
            window.addEventListener('DOMContentLoaded', callback, false);
        }
    }
};

module.exports = Runner;

},{}],2:[function(require,module,exports){
var BigLink = React.createClass({
  displayName: "BigLink",

  protTypes: {
    label: React.PropTypes.string.isRequired,
    icon: React.PropTypes.string.isRequired,
    click: React.PropTypes.string.isRequired,
    key: React.PropTypes.number.isRequired,
    id: React.PropTypes.number.isRequired,
    sublabel: React.PropTypes.string
  },
  onClick: function (data, e) {
    this.props.click(data, e);
  },
  render: function () {
    var sublabel = "";

    if (typeof this.props.sublabel !== 'undefined') {
      sublabel = React.createElement(
        "div",
        { className: "desc" },
        this.props.sublabel
      );
    }

    return React.createElement(
      "div",
      { className: "col-md-4 biglink" },
      React.createElement(
        "div",
        { className: "info-box  bg-warning  text-white" },
        React.createElement(
          "a",
          { style: { cursor: 'pointer' }, onClick: this.onClick.bind(null, this.props), className: "dashboard-stat green-soft dashboard-stat-light" },
          React.createElement(
            "div",
            { className: "visual" },
            React.createElement("i", { className: this.props.icon })
          ),
          React.createElement(
            "div",
            { className: "details" },
            React.createElement(
              "div",
              { className: "number links" },
              this.props.label
            ),
            sublabel
          )
        )
      )
    );
  }
});

module.exports = BigLink;

},{}],3:[function(require,module,exports){
var LinkList = require('./link_list.js');
var Runner = require('../components/runner.js');
var SingleAbmTable = require('./single_abm_table.js');

var NotificationsEmail = {
    onLinkClick: function (data, e) {
        e.preventDefault();

        var title = React.createElement(
            'span',
            null,
            data.label,
            ' ',
            React.createElement(
                'small',
                null,
                data.sublabel
            )
        );

        var table = ReactDOM.render(React.createElement(SingleAbmTable, {
            env: data,
            field: $ReactData.abm.field,
            fieldLabel: $ReactData.abm.fieldLabel,
            fieldIcon: $ReactData.abm.fieldIcon,
            feedApi: $ReactData.abm.feedApi,
            pushApi: $ReactData.abm.pushApi,
            dropApi: $ReactData.abm.dropApi,
            editApi: $ReactData.abm.editApi,
            icon: data.icon,
            title: title,
            emptyText: $ReactData.abm.emptyText }), document.getElementById('table'));

        table.start();
    }
};

Runner.start(function () {
    for (var i in $ReactData.links) {
        $ReactData.links[i].click = NotificationsEmail.onLinkClick;
    }

    ReactDOM.render(React.createElement(
        'div',
        null,
        React.createElement(LinkList, { links: $ReactData.links }),
        React.createElement('div', { id: 'table' })
    ), document.getElementById('react-root'));
});

},{"../components/runner.js":1,"./link_list.js":4,"./single_abm_table.js":6}],4:[function(require,module,exports){
var BigLink = require('./biglink.js');

var LinkList = React.createClass({
    displayName: "LinkList",

    propTypes: {
        links: React.PropTypes.array.isRequired
    },
    render: function () {
        return React.createElement(
            "div",
            { className: "row" },
            React.createElement(
                "div",
                { className: "col-md-12 bigsinglesport" },
                React.createElement(
                    "div",
                    { className: "portlet box" },
                    React.createElement(
                        "div",
                        { className: "portlet-body" },
                        React.createElement(
                            "div",
                            { className: "row" },
                            " ",
                            this.props.links.map(function (link, i) {
                                return React.createElement(BigLink, { id: link.id, key: link.id, icon: link.icon, click: link.click, label: link.label, sublabel: link.sublabel });
                            }),
                            " "
                        )
                    )
                )
            )
        );
    }
});

module.exports = LinkList;

},{"./biglink.js":2}],5:[function(require,module,exports){
var SingleAbmRow = React.createClass({
    displayName: "SingleAbmRow",

    protTypes: {
        id: React.PropTypes.string.isRequired,
        value: React.PropTypes.string.isRequired,
        dropApi: React.PropTypes.string.isRequired,
        editApi: React.PropTypes.string.isRequired,
        env: React.PropTypes.array.isRequired,
        dropCallback: React.PropTypes.func.isRequired,
        editCallback: React.PropTypes.func.isRequired
    },
    states: {
        idle: 0,
        edit: 1,
        drop: 2,
        editSend: 3,
        dropSend: 4,
        editFail: 5,
        dropFail: 6
    },
    getInitialState: function () {
        return {
            action: this.states.idle,
            input: this.props.value
        };
    },
    apiLag: 2000,
    valueColWidth: "75%",
    resolvState: function () {
        var state = this.states.idle;

        if (this.state !== null) {
            if (typeof this.state.action !== 'undefined') {
                switch (this.state.action) {
                    case this.states.edit:
                    case this.states.drop:
                    case this.states.idle:
                    case this.states.editSend:
                    case this.states.dropSend:
                    case this.states.editFail:
                    case this.states.dropFail:
                        state = this.state.action;
                        break;
                }
            }
        }

        return state;
    },
    resolvUI: function () {
        var state = this.resolvState();
        var ui = "";

        switch (state) {
            case this.states.edit:
                ui = this.resolvEditUI();
                break;
            case this.states.idle:
                ui = this.resolvIdleUI();
                break;
            case this.states.drop:
                ui = this.resolvDropUI();
                break;
            case this.states.dropSend:
                ui = this.resolvDropSendUI();
                break;
            case this.states.editSend:
                ui = this.resolvEditSendUI();
                break;
            case this.states.editFail:
                ui = this.resolvEditFailUI();
                break;
            case this.states.dropFail:
                ui = this.resolvDropFailUI();
                break;
        }

        return ui;
    },
    resolvEditDivClass: function () {
        var className = "input-icon right";

        if (this.isFail()) {
            className += " has-error";
        }

        return className;
    },
    resolvErrorMessage: function () {
        var error = "";

        if (this.isFail()) {
            error = React.createElement(
                "div",
                { style: { color: "#AF0000" } },
                this.state.message
            );
        }

        return error;
    },
    isFail: function () {
        var isFail = false;

        if (this.state !== null) {
            if (typeof this.state.action !== 'undefined') {
                switch (this.state.action) {
                    case this.states.editFail:
                    case this.states.dropFail:
                        isFail = true;
                        break;
                }
            }
        }

        return isFail;
    },
    resolvEditUI: function () {
        var value = this.state.input;
        var inputDivClass = this.resolvEditDivClass();
        var errorMessage = this.resolvErrorMessage();

        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                React.createElement(
                    "div",
                    { className: inputDivClass },
                    React.createElement("i", { className: "fa fa-edit" }),
                    React.createElement("input", { className: "form-control form-cascade-control", type: "text", defaultValue: value, ref: ref => this.input = ref })
                ),
                errorMessage
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement(
                    "button",
                    { onClick: this.editConfirmClick.bind(null, this.props), className: "btn btn-success", style: { cursor: 'pointer' } },
                    "EDITAR"
                ),
                React.createElement(
                    "button",
                    { onClick: this.editCancelClick.bind(null, this.props), className: "btn btn-danger", style: { cursor: 'pointer' } },
                    "CANCELAR"
                )
            )
        );

        return ui;
    },
    resolvEditFailUI: function () {
        return this.resolvEditUI();
    },
    resolvEditSendUI: function () {
        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                React.createElement("input", { disabled: "disabled", className: "form-control", type: "text", value: this.state.input, ref: ref => this.input = ref })
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement("img", { src: "/img/flipflop.gif", width: "30px", height: "30px" })
            )
        );

        return ui;
    },
    resolvDropSendUI: function () {
        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                this.props.value
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement("img", { src: "/img/flipflop.gif", width: "30px", height: "30px" })
            )
        );

        return ui;
    },
    resolvDropFailUI: function () {
        var errorMessage = this.resolvErrorMessage();

        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                this.props.value,
                errorMessage
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement(
                    "button",
                    { onClick: this.dropConfirmClick.bind(null, this.props), className: "btn btn-success", style: { cursor: 'pointer' } },
                    "ELIMINAR"
                ),
                React.createElement(
                    "button",
                    { onClick: this.dropCancelClick.bind(null, this.props), className: "btn btn-danger", style: { cursor: 'pointer' } },
                    "CANCELAR"
                )
            )
        );

        return ui;
    },
    resolvDropUI: function () {
        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                this.props.value
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement(
                    "button",
                    { onClick: this.dropConfirmClick.bind(null, this.props), className: "btn btn-success", style: { cursor: 'pointer' } },
                    "ELIMINAR"
                ),
                React.createElement(
                    "button",
                    { onClick: this.dropCancelClick.bind(null, this.props), className: "btn btn-danger", style: { cursor: 'pointer' } },
                    "CANCELAR"
                )
            )
        );

        return ui;
    },
    resolvIdleUI: function () {
        var ui = React.createElement(
            "tr",
            null,
            React.createElement(
                "td",
                { width: this.valueColWidth },
                this.props.value
            ),
            React.createElement(
                "td",
                { style: { textAlign: 'right' } },
                React.createElement(
                    "button",
                    { onClick: this.editClick.bind(null, this.props), ref: ref => this.editButton = ref, className: "btn btn-warning", style: { cursor: 'pointer' } },
                    React.createElement("i", { className: "fa fa-edit" })
                ),
                React.createElement(
                    "button",
                    { onClick: this.dropClick.bind(null, this.props), ref: ref => this.dropButton = ref, className: "btn btn-warning", style: { cursor: 'pointer' } },
                    React.createElement("i", { className: "fa fa-ban" })
                )
            )
        );

        return ui;
    },
    input: null,
    idle: function (id, value) {
        this.setState({
            'action': this.states.idle,
            'id': id,
            'value': value
        });
    },
    editButton: null,
    editClick: function (data, event) {
        this.edit();
    },
    editConfirmClick: function (data, event) {
        this.confirmEdit();
    },
    confirmEdit: function () {
        this.editSend();
    },
    editCancelClick: function (data, event) {
        this.idle();
    },
    edit: function () {
        if (this.editButton !== null) {
            this.setState({
                'action': this.states.edit,
                'input': this.props.value
            }, function () {
                this.input.focus();
            });
        }
    },
    dropButton: null,
    dropClick: function (data, event) {
        this.drop();
    },
    dropConfirmClick: function (data, event) {
        this.dropSend();
    },
    dropCancelClick: function (data, event) {
        this.idle();
    },
    drop: function () {
        if (this.dropButton !== null) {
            this.setState({
                'action': this.states.drop
            });
        }
    },
    dropSend: function () {
        this.setState({
            'action': this.states.dropSend
        });

        var self = this;

        this.timer = setTimeout(function () {
            self.dropSendAjax();
        }, this.apiLag);
    },
    editSend: function () {
        this.setState({
            'action': this.states.editSend,
            'input': this.input.value
        });

        var self = this;

        this.timer = setTimeout(function () {
            self.editSendAjax();
        }, this.apiLag);
    },
    dropSendAjax: function () {
        this.ajaxHit();

        var self = this;

        this.xhr = $.ajax({
            url: this.props.dropApi,
            method: 'POST',
            data: {
                id: this.props.id,
                env: this.props.env
            }
        }).success(function (data) {
            self.dropAjaxSuccess(data);
        }).error(function (event) {
            self.dropAjaxError(event);
        }).always(function () {
            self.xhr = null;
        });
    },
    dropAjaxSuccess: function (data) {
        this.idle();
        this.props.dropCallback(data);
    },
    dropAjaxError: function (event) {
        this.idle();
        this.dropFail(event.responseText);
    },
    editAjaxError: function (event) {
        this.editFail(event.responseText);
    },
    editSendAjax: function () {
        this.ajaxHit();

        var self = this;

        this.xhr = $.ajax({
            url: this.props.editApi,
            method: 'POST',
            data: {
                id: this.props.id,
                value: this.state.input,
                env: this.props.env
            }
        }).success(function (data) {
            self.editAjaxSuccess(data);
        }).error(function (event) {
            self.editAjaxError(event);
        }).always(function () {
            self.xhr = null;
        });
    },
    editAjaxSuccess: function (data) {
        var value = null;
        var id = null;

        for (var i in data) {
            value = data[i];
            id = i;
            break;
        }

        if (value == null || id == null) {
            this.editAjaxError({
                responseText: 'No se obtuvo una respuesta valida del Servidor'
            });
        } else {
            this.idle();
            this.props.editCallback(id, value);
        }
    },
    editAjaxError: function (event) {
        this.editFail(event.responseText);
    },
    dropFail: function (message) {
        this.setState({
            action: this.states.dropFail,
            message: message
        });
    },
    editFail: function (message) {
        this.setState({
            action: this.states.editFail,
            message: message
        });
    },
    xhr: null,
    ajaxHit: function () {
        if (this.xhr !== null) {
            this.xhr.abort();
        }
    },
    render: function () {
        var ui = this.resolvUI();

        return ui;
    }
});

module.exports = SingleAbmRow;

},{}],6:[function(require,module,exports){
var SingleAbmRow = require('./single_abm_row.js');

var SingleAbmTable = React.createClass({
    displayName: 'SingleAbmTable',

    protTypes: {
        icon: React.PropTypes.string.isRequired,
        title: React.PropTypes.string.isRequired,
        field: React.PropTypes.string.isRequirFed,
        fieldLabel: React.PropTypes.string.isRequired,
        fieldIcon: React.PropTypes.string.isRequired,
        feedApi: React.PropTypes.string.isRequired,
        pushApi: React.PropTypes.string.isRequired,
        dropApi: React.PropTypes.string.isRequired,
        editApi: React.PropTypes.string.isRequired,
        emptyText: React.PropTypes.string.isRequired,
        env: React.PropTypes.array
    },
    apiLag: 2000,
    start: function () {
        this.cleanSleepContent();
        this.cleanSleepPush();
        this.cleanError();
        this.cleanXhrContent();
        this.cleanXhrPush();

        this.setState({
            'content': this.contentStates.idle,
            'push': this.pushStates.idle,
            'data': []
        });

        this.fetch();
    },
    contentStates: {
        idle: -1,
        fetching: 0,
        empty: 1,
        filled: 2
    },
    pushStates: {
        idle: 0,
        sending: 1,
        failed: 2
    },
    error: null,
    setError: function (message) {
        this.error = message;
    },
    cleanError: function () {
        this.error = null;
    },
    resolvError: function () {
        var error = "";

        if (this.error !== null) {
            error = React.createElement(
                'div',
                { className: 'alert alert-danger' },
                React.createElement(
                    'strong',
                    null,
                    'Error! '
                ),
                this.error
            );
        }

        return error;
    },
    input: null,
    xhrPush: null,
    pushButton: null,
    push: function (data, e) {
        if (this.pushButton == null) {
            return;
        }

        if (this.xhrAdd != null) {
            return;
        }

        this.pushSend();
    },
    pushIdle: function (data) {
        var content = this.contentStates.empty;

        if (this.hasData(data)) {
            content = this.contentStates.filled;
        }

        this.setState({
            'content': content,
            'push': this.pushStates.idle,
            'data': data
        });
    },
    pushFail: function (message) {
        this.setState({
            'push': this.pushStates.failed,
            'message': message
        });
    },
    pushSend: function () {
        this.cleanError();

        this.setState({
            'push': this.pushStates.sending
        });

        var self = this;

        this.sleepPush = setTimeout(function () {
            self.pushApi();
        }, this.apiLag);
    },
    cleanSleepPush: function () {
        if (this.sleepPush != null) {
            clearTimeout(this.sleepPush);
        }

        this.sleepContent = null;
    },
    sleepPush: null,
    readEnv: function () {
        var env = {};

        if (typeof this.props.env !== 'undefined') {
            for (var i in this.props.env) {
                var v = this.props.env[i];

                if (typeof v !== 'function') {
                    env[i] = v;
                }
            }
        }

        return env;
    },
    pushApi: function () {
        var self = this;
        var envData = this.readEnv();

        this.xhrPush = $.ajax({
            url: this.props.pushApi,
            method: 'POST',
            data: {
                env: envData,
                value: this.input.value
            }
        }).success(function (data) {
            self.pushAjaxSuccess(data);
        }).error(function (event) {
            self.pushAjaxError(event);
        }).always(function () {
            self.xhrPush = null;
        });
    },
    pushAjaxSuccess: function (data) {
        var newData = this.state.data;

        if (this.hasData(data)) {
            for (var id in data) {
                var value = data[id];
                newData[id] = value;
                break;
            }
        }

        this.input.value = "";
        this.pushIdle(newData);
    },
    pushAjaxError: function (event) {
        switch (event.status) {
            case 406:
                this.pushFail(event.responseText);
                break;
            case 500:
                this.setError("Falló la comunicación con el Servidor");
                this.pushIdle(this.state.data);
                break;
        }
    },
    cleanSleepContent: function () {
        if (this.sleepContent != null) {
            clearTimeout(this.sleepContent);
        }

        this.sleepContent = null;
    },
    sleepContent: null,
    fetch: function () {
        this.cleanError();

        this.setState({
            'content': this.contentStates.fetching
        });

        var self = this;
        this.cleanSleepContent();

        this.sleepContent = setTimeout(function () {
            self.feedApi();
        }, this.apiLag);
    },
    xhrContent: null,
    cleanXhrContent: function () {
        if (this.xhrContent !== null) {
            this.xhrContent.abort();
        }

        this.xhrContent = null;
    },
    cleanXhrPush: function () {
        if (this.xhrPush !== null) {
            this.xhrPush.abort();
        }

        this.xhrPush = null;
    },
    contentHit: function () {
        this.cleanXhrContent();
        this.cleanError();
    },
    feedApi: function () {
        var self = this;
        var envData = this.readEnv();
        self.contentHit();

        this.xhrContent = $.ajax({
            url: this.props.feedApi,
            type: 'post',
            data: {
                env: envData
            }
        }).success(function (data) {
            self.feedApiSuccess(data);
        }).error(function (event) {
            self.feedApiError(event);
        }).always(function () {
            self.xhrContent = null;
        });
    },
    feedApiError: function (event) {
        this.setError("Falló la comunicación con el Servidor");
        this.empty();
    },
    hasData: function (data) {
        var hasData = false;

        /* Una forma poco ortodoxa de saber si hay algo en un array, pero
         * funciona tambien para singletons. */

        for (var i in data) {
            hasData = true;
            break;
        }

        return hasData;
    },
    feedApiSuccess: function (data) {
        try {
            if (this.hasData(data)) {
                this.fill(data);
            } else {
                this.empty();
            }
        } catch (e) {
            this.feedApiError();
        }
    },
    empty: function () {
        this.setState({
            'content': this.contentStates.empty
        });
    },
    fill: function (data) {
        this.setState({
            'content': this.contentStates.filled,
            'data': data
        });
    },
    resolvContent: function () {
        var content = "";
        var state = this.contentStates.idle;

        if (this.state !== null) {
            if (typeof this.state.content !== 'undefined') {
                state = this.state.content;
            }
        }

        switch (state) {
            case this.contentStates.fetching:
                content = this.fetchingContent();
                break;
            case this.contentStates.empty:
                content = this.emptyContent();
                break;
            case this.contentStates.filled:
                content = this.filledContent();
                break;
        }

        return content;
    },
    fetchingContent: function () {
        return React.createElement(
            'div',
            { className: 'portlet-body' },
            React.createElement(
                'div',
                { id: 'single_abm_fetch', className: 'row' },
                React.createElement(
                    'div',
                    { className: 'col-md-12', style: { padding: "40px 0px 40px 0px", textAlign: 'center' } },
                    React.createElement('img', { src: '/img/flipflop.gif', width: '30px', height: '30px' })
                )
            )
        );
    },
    emptyContent: function () {
        return React.createElement(
            'div',
            { className: 'portlet-body' },
            React.createElement(
                'div',
                { id: 'single_abm_empty', className: 'row' },
                React.createElement(
                    'div',
                    { className: 'col-md-12' },
                    React.createElement(
                        'p',
                        null,
                        this.props.emptyText
                    )
                )
            )
        );
    },
    getRows: function () {
        var data = this.state.data;

        return React.createElement(
            'tbody',
            null,
            data.map((value, i) => {
                return React.createElement(SingleAbmRow, {
                    dropApi: this.props.dropApi,
                    editApi: this.props.editApi,
                    dropCallback: this.dropCallback,
                    editCallback: this.editCallback,
                    key: i,
                    id: i,
                    env: this.readEnv(),
                    value: value });
            })
        );
    },
    dropErrorCallback: function (message) {
        this.error = message;
        this.forceUpdate();
    },
    editCallback: function (id, value) {
        var push = this.pushStates.idle;
        var content = this.contentStates.filled;
        var newData = [];

        if (typeof this.state.data !== null) {
            newData = this.state.data;
        }

        newData[id] = value;

        if (!this.hasData(newData)) {
            content = this.contentStates.empty;
        }

        if (typeof this.state.push !== null) {
            push = this.state.push;
        }

        this.setState({
            'content': content,
            'push': push,
            'data': newData
        });
    },
    dropCallback: function (newData) {
        var push = this.pushStates.idle;
        var content = this.contentStates.filled;

        if (!this.hasData(newData)) {
            content = this.contentStates.empty;
        }

        if (typeof this.state.push !== null) {
            push = this.state.push;
        }

        this.setState({
            'content': content,
            'push': push,
            'data': newData
        });
    },
    filledContent: function () {
        var rows = this.getRows();

        return React.createElement(
            'div',
            { id: 'single_abm_fill' },
            React.createElement(
                'div',
                { className: 'portlet-body' },
                React.createElement(
                    'table',
                    { className: 'striped table dataTable' },
                    rows
                )
            )
        );
    },
    resolvPushUI: function () {
        var state = this.pushStates.idle;
        var ui = "";

        if (this.state !== null) {
            if (typeof this.state.push !== 'undefined') {
                state = this.state.push;
            }
        }

        switch (state) {
            case this.pushStates.idle:
                ui = this.pushIdleUI();
                break;
            case this.pushStates.sending:
                ui = this.pushSendingUI();
                break;
            case this.pushStates.failed:
                ui = this.pushFailedUI();
                break;
        }

        return ui;
    },
    pushButtonUI: function () {
        return React.createElement(
            'div',
            { className: 'col-md-2' },
            React.createElement(
                'div',
                { className: 'input-icon right' },
                React.createElement(
                    'button',
                    { ref: ref => this.pushButton = ref, onClick: this.push.bind(null, null), className: 'btn blue-hoki', style: { width: 100 + '%' }, id: 'list_btn' },
                    React.createElement('i', { className: 'fa fa-plus' })
                )
            )
        );
    },
    pushSendingUI: function () {
        return React.createElement(
            'div',
            { className: 'row' },
            this.pushInputUI(),
            React.createElement(
                'div',
                { className: 'col-md-2' },
                React.createElement(
                    'div',
                    { className: 'input-icon right' },
                    React.createElement(
                        'center',
                        null,
                        React.createElement('img', { src: '/img/flipflop.gif', width: '30px', height: '30px' })
                    )
                )
            )
        );
    },
    pushFailedUI: function () {
        return React.createElement(
            'div',
            { className: 'row' },
            this.pushInputUI(),
            this.pushButtonUI()
        );
    },
    pushIdleUI: function () {
        return React.createElement(
            'div',
            { className: 'row' },
            this.pushInputUI(),
            this.pushButtonUI()
        );
    },
    resolvPushExtras: function () {
        var extras = {
            disabled: '',
            className: 'input-icon right'
        };

        if (this.state !== null) {
            switch (this.state.push) {
                case this.pushStates.idle:
                    break;
                case this.pushStates.sending:
                    extras.disabled = 'disabled';
                    break;
                case this.pushStates.failed:
                    extras.className += ' has-error';
                    extras.message = React.createElement(
                        'span',
                        { style: { color: '#AF0000' } },
                        this.state.message
                    );
                    break;
            }
        }

        return extras;
    },
    pushInputUI: function () {
        var extras = this.resolvPushExtras();

        return React.createElement(
            'div',
            { className: 'col-md-10' },
            React.createElement(
                'div',
                { className: extras.className },
                React.createElement('i', { className: 'fa fa-edit' }),
                React.createElement('input', { disabled: extras.disabled, ref: ref => this.input = ref, type: 'text', className: 'form-cascade-control form-control', id: 'single_abm_push_input' }),
                extras.message
            )
        );
    },
    render: function () {
        var content = this.resolvContent();
        var error = this.resolvError();
        var pushUI = this.resolvPushUI();

        return React.createElement(
            'div',
            { className: 'row' },
            React.createElement(
                'div',
                { className: 'col-md-12' },
                error,
                React.createElement(
                    'div',
                    { className: 'portlet light form-group' },
                    React.createElement(
                        'div',
                        { className: 'portlet-title' },
                        React.createElement(
                            'div',
                            { className: 'caption' },
                            React.createElement(
                                'span',
                                { className: 'caption-subject bold uppercase' },
                                ' ',
                                this.props.fieldLabel,
                                ' '
                            ),
                            React.createElement(
                                'span',
                                null,
                                React.createElement(
                                    'small',
                                    null,
                                    this.props.title
                                )
                            )
                        )
                    ),
                    pushUI,
                    content
                )
            )
        );
    }
});

module.exports = SingleAbmTable;

},{"./single_abm_row.js":5}]},{},[3]);
