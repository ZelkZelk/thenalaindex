var Runner = {    
    run: function(callback){        
        callback();
    },
    start: function(callback){
        const loadedStates = ['complete', 'loaded', 'interactive'];

        if (loadedStates.includes(document.readyState) && document.body) {
            callback();
        } 
        else {
            window.addEventListener('DOMContentLoaded', callback, false);
        }

    }
};

module.exports = Runner;