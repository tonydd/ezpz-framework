/**
 * EZ class to provide Framework functions
 * @constructor
 */
function EZ() {
    this.data = {};
    this.baseUrl = '%baseUrl%';
    this.pDelimiter = '%pDelimiter%';
    this.fDelimiter = '%fDelimiter%';
    this.encodedParamName = '%encodedParamName%';
}

/**
 * Global static variables
 */
EZ.workerUrl = '%workerUrl%';

/**
 *
 * @param key
 * @param data
 */
EZ.prototype.setData = function (key, data)
{
    this.data[key] = data;
};

/**
 *
 * @param key
 * @returns {*}
 */
EZ.prototype.getData = function(key)
{
    return this.data[key];
};

/**
 *
 * @param controller
 * @param action
 * @param parameters
 * @returns {string}
 */
EZ.prototype.buildUrl = function (controller, action, parameters, doNotEncode) {
    var url = this.baseUrl + "?ctl=" + controller + "&action=" + action;

    parameters = parameters || null;
    doNotEncode = doNotEncode || false;
    if (parameters !== null) {
        if (doNotEncode) {
            for(var key in parameters) {
                url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]);
            }
        }
        else {
            url += "&"+this.encodedParamName+"=" + btoa(EZ.arrToPlain(parameters));
        }
    }

    return url;
};

/**
 *
 * @param arr
 * @returns {string}
 */
EZ.arrToPlain = function(arr)
{
    var out = '';
    for (var key in arr) {
        out += encodeURIComponent(key);
        out += Ez.fDelimiter;
        out += encodeURIComponent(JSON.stringify(arr[key]));
        out += Ez.pDelimiter;
    }

    out =  out.slice(0, -1);
    return out;
}

/**
 * Super global
 * @type {EZ}
 */
Ez = new EZ();

/****************************************
 * Service worker instantiation
 ****************************************/

/*
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(EZ.workerUrl, {scope: Ez.baseUrl}).then(function(reg) {
        // registration worked
        console.log('Registration succeeded. Scope is ' + reg.scope);
    }).catch(function(error) {
        // registration failed
        console.log('Registration failed with ' + error);
    });
} else {
    alert('Ça va pas être possible')
}
*/