var HttpClient = function(){
    this.xhr = null;
    this.data = null;
    this.url = null;
    this.dataType = null;
    this.type = null;
    this.doneCallback = null;
    this.errorCallback = null;
    this.alwaysCallback = null;
    this.response = null;
    this.error = false;

    this.postJson = function(url,data,callbacks){
        this.data = data;
        this.url = url;
        this.type = 'POST';
        this.dataType = 'json';

        if(typeof callbacks.error !== 'undefined'){
            this.errorCallback = callbacks.error;
        }

        if(typeof callbacks.done !== 'undefined'){
            this.doneCallback = callbacks.done;
        }

        if(typeof callbacks.always !== 'undefined'){
            this.alwaysCallback = callbacks.always;
        }

        this.request(data);
    };

    this.getJson = function(url,callbacks){
        this.data = [];
        this.url = url;
        this.type = 'GET';
        this.dataType = 'json';

        if(typeof callbacks.error !== 'undefined'){
            this.errorCallback = callbacks.error;
        }

        if(typeof callbacks.done !== 'undefined'){
            this.doneCallback = callbacks.done;
        }

        if(typeof callbacks.always !== 'undefined'){
            this.alwaysCallback = callbacks.always;
        }

        this.request();
    };

    this.getResponse = function(){
        return response;
    };

    this.abort = function(){
        if(this.xhr !== null){
            this.xhr.abort();
        }
    };

    this.request = function(){
        var xhr = new XMLHttpRequest();
        var self = this;
        this.error = true

        xhr.open(this.type, encodeURI(this.url));
        xhr.onload = function(){
            if (this.status === 200) {
                var data = self.getRequestData();
                self.response = data;
                self.error = false;
            }

            var error = self.hasError();

            if(error !== false){
                if(self.errorCallback !== null){
                    self.errorCallback(xhr,xhr.statusText,error);
                }
            }
            else{
                if(self.doneCallback !== null){
                    self.doneCallback(self.response);
                }
            }

            if(self.alwaysCallback !== null){
                self.alwaysCallback();
            }
        };

        this.xhr = xhr;

        if(this.type === 'POST'){
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send(this.getPostData());
        }
        else{
            xhr.send();
        }
    }

    this.getPostData = function(){
        var data = '';

        for(var field in this.data){
            var value = this.data[field];
            data += field + "=" + value + "&";
        }

        return data.substr(0,data.length - 1);
    }

    this.getRequestData = function(){
        var data;

        switch(this.dataType){
            case 'json':
                data = this.jsonParse(this.xhr.responseText);
                break;
            default:
                data = this.xhr.responseText;
                break;
        }

        return data;
    }

    this.jsonParse = function(json){
        try{
            return JSON.parse(json);
        }
        catch(e){
            this.error = e;
            return null;
        }
    }

    this.hasError = function(){
        return this.error;
    }
};

module.exports = HttpClient;
