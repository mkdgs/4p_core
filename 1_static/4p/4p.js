"use strict";
/*
 * le namespace fp- est utilisé pour le css le namespace fp. est utilisé pour
 * javascript
 */
var $4p;
if (!$4p) {

    // polyfill & Cie 

    // ES5 15.4.4.18 Array.prototype.forEach ( callbackfn [ , thisArg ] )
    // From https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/forEach
    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function (fun /*, thisp */) {
            "use strict";

            if (this === void 0 || this === null) {
                throw new TypeError();
            }

            var t = Object(this);
            var len = t.length >>> 0;
            if (typeof fun !== "function") {
                throw new TypeError();
            }

            var thisp = arguments[1], i;
            for (i = 0; i < len; i++) {
                if (i in t) {
                    fun.call(thisp, t[i], i, t);
                }
            }
        };
    }

    // Ie8 compatibility filter is a JavaScript extension to the ECMA-262
    // standard
    // https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/filter
    if (!Array.prototype.filter) {
        Array.prototype.filter = function (fun, thisp) {
            "use strict";
            if (this == null)
                throw new TypeError();
            var t = Object(this);
            var len = t.length >>> 0;
            if (typeof fun != "function")
                throw new TypeError();
            var res = [];
            var thisp = arguments[1];
            for (var i = 0; i < len; i++) {
                if (i in t) {
                    var val = t[i]; // in case fun mutates this
                    if (fun.call(thisp, val, i, t))
                        res.push(val);
                }
            }
            return res;
        };
    }

    if (typeof String.prototype.trim !== 'function') {
        String.prototype.trim = function () {
            return this.replace(/^\s+|\s+$/, '') + '';
        };
    }
    // addslashes()
    if (typeof String.prototype.addslashes !== 'function') {
        String.prototype.addslashes = function () {
            return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0')
                    + '';
        };
    }
    // striptags
    if (typeof String.prototype.striptags != 'function') {
        String.prototype.striptags = function () {
            return this.replace(/(<([^>]+)>)/gi, "");
        };
    }
    // getInt()
    if (typeof String.prototype.getInt !== 'function') {
        String.prototype.getInt = function () {
            var rgx = new RegExp('[0-9]+', 'g'), r;
            if (r = rgx.exec(this))
                return parseInt(r);
            else
                return Number.NaN;
        };
    }
    // isAlphaNumeric
    if (typeof String.prototype.isAlphaNumeric !== 'function') {
        String.prototype.isAlphaNumeric = function () {
            var rgx = new RegExp('^[a-z0-9_-]+$', 'gi');
            if (rgx.test(this))
                return true;
            return false;
        };
    }

    if (typeof String.prototype.formatNumber !== 'function') {
        // french version of http://phpjs.org/functions/this_format:481
        String.prototype.formatNumber = function (decimals, dec_point,
                thousands_sep) {
            var number = this.replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0
                    : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? ' '
                    : thousands_sep, dec = (typeof dec_point === 'undefined') ? ','
                    : dec_point, s = '', toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3)
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        };
    }


    $4p = (function ($) {
        return {
            // backward and tricks
            fakeStorage: function () {
                var d = {
                    data: [],
                    key: function () {
                    },
                    getItem: function (key) {
                        return (d[key]) ? d[key] : null;
                    },
                    setItem: function (key, data) {
                        d[key] = data;
                    },
                    removeItem: function (key) {
                        (d[key]) ? unset(d[key]) : null;
                    },
                    clear: function () {
                        d = [];
                    }
                };
                return d;
            },
            // configuration
            cfg: {
                throbber: {
                    'enabled': true
                }
            },
            events: {},
            parentInstance: function () {
                try {
                    if (typeof window.parent.$4p != "undefined") {
                        return window.parent.$4p;
                    }
                } catch (e) {
                    $4p.log(e);
                }
                ;
                return $4p;
            },
            jquery: window.jQuery,
            jqueryEscapeSelector: function (selector) {
                // escape: !"#$%&'()*+,./:;<=>?@[\]^`{|}~\
                var rgx = new RegExp(
                        '(["#$%&\'\(\)\*,\./:;<=>\?@\\[\\]\^`\{\|\}~\!\+\\\\])',
                        'g');
                return selector.replace(rgx, '\\\\$1');
            },
            document: document,
            window: window,
            protocol: function () {
                return window.location.protocol;
            },
            globals: {},
            glob: function (name, val) {
                if (typeof val != 'undefined')
                    return $4p.globals[name] = val;
                if (typeof $4p.globals[name] != 'undefined')
                    return $4p.globals[name];
            },
            baseUrl: function () {
                var baseUrl;
                if (baseUrl)
                    return baseUrl;
                if (baseUrl = $('base').attr('href'))
                    return baseUrl;
                return location.href.substring(0, location.href
                        .lastIndexOf('/') + 1);
            },
            log: function (msg) {
                if (typeof console == "object")
                    console.log(msg);
            },
            pageLock: function (unlock) {
                if (unlock)
                    $(window).unbind('beforeunload');
                else
                    $(window).bind('beforeunload', function () {
                        return true;
                    });
            },
            windowOpen: function(url, data, verb, target) {               
                    var form = document.createElement("form");
                    form.action = url;
                    form.method = verb || 'POST';
                    form.target = target || "_self";
                    if (data) {
                      for (var key in data) {
                        var input = document.createElement("textarea");
                        input.name = key;
                        input.value = typeof data[key] === "object" ? JSON.stringify(data[key]) : data[key];
                        form.appendChild(input);
                      }
                    }
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();               
            },
            jsonEncode: (function () {
                if (!$ || !($.toJSON || Object.toJSON || window.JSON)) {
                    throw new Error("jQuery needs to be loaded before encode");
                }
                return $.toJSON || Object.toJSON
                        || (window.JSON && (JSON.encode || JSON.stringify));
            })(),
            jsonDecode: (function () {
                return $.evalJSON
                        || (window.JSON && (JSON.decode || JSON.parse))
                        || function (str) {
                            return String(str).evalJSON();
                        };
            })(),
            // a tester unifier le storage sur l'instance $4p
            sessionStorage: (function () {
                try {
                    return (window.sessionStorage) ? window.sessionStorage
                            : $4p.fakeStorage();
                } catch (e) {
                    return $4p.fakeStorage();
                }
            })(),
            localStorage: (function () {
                try {
                    return (window.localStorage) ? window.localStorage : $4p
                            .fakeStorage();
                } catch (e) {
                    return $4p.fakeStorage();
                }
            })(),
            pageUnlock: function () {
                $(window).unbind('beforeunload');
            },
            parseInt: function (string) {
                return string.getInt();
            },
            uniquId: function () {
                if (typeof $4p.incUniquid == 'undefined') {
                    $4p.incUniquid = 1;
                }
                ;
                $4p.incUniquid++;
                var d = new Date().getTime();
                return $4p.incUniquid + '' + d;
            },
            skipCache: function () {
                if (parseInt($4p.glob('cache')))
                    return '?_=' + Math.random();
                if (parseInt($4p.glob('version')))
                    return '?v=' + $4p.glob('version');
                return '';
            },
            tpl: function (tpl) {
                var f = {};
                if (typeof tpl == 'string') {
                    if (tpl.substring(0, 4) == 'http') {
                        $.ajax({
                            cache: true,
                            url: tpl + $4p.skipCache(),
                            dataType: 'text',
                            async: false,
                            success: function (d) {
                                f.tpl = d;
                            }
                        });
                    } else
                        f.tpl = tpl;
                    // test scope part                  
                    f.scope = {root: f.tpl};
                    f.element = $(f.tpl);
                    f.element.find('[data-fp-scope]').each(function () {
                        f.scope[$(this).attr('data-fp-scope')] = $('<div />').append($(this)).html();
                    });
                } else {
                    $4p.log('unknow tpl type ' + typeof tpl);
                    return;
                }
                f.cache = {};
                f.render = function (data, scope) {
                    // /http://www.west-wind.com/weblog/posts/509108.aspx
                    // / <summary>
                    // / Client side template parser that uses ~#= #~ and ~#
                    // code #~ expressions.
                    // / and # # code blocks for template expansion.
                    // / NOTE: chokes on single quotes in the document in some
                    // situations
                    // / use &amp;rsquo; for literals in text and avoid any
                    // single quote
                    // / attribute delimiters.
                    // / </summary>
                    // / <param name="str" type="string">The text of the
                    // template to expand</param>
                    // / <param name="data" type="var">
                    // / Any data that is to be merged. Pass an object and
                    // / that object's properties are visible as variables.
                    // / </param>
                    // / <returns type="string" />
                    var err = "", func;

                    if (!scope)
                        scope = 'root';
                    try {
                        if (typeof f.cache[scope] != 'function') {
                            var strFunc = "var p=[],print=function(){p.push.apply(p,arguments);};"
                                    + "with(obj){p.push('"
                                    + f.tpl.replace(/[\r\t\n]/g, " ")
                                    .split("'")
                                    .join("\\'")
                                    .replace(/{{=(.+?)}}/g, "',$1,'")
                                    .split("{{")
                                    .join("');")
                                    .split("}}")
                                    .join("p.push('") + "');}return p.join('');";
                            f.cache[scope] = new Function("obj", strFunc);
                        }
                        return f.cache[scope](data);
                    } catch (e) {
                        err = e.message;
                    }
                    return "< # ERROR: " + err + scope + "-- ok # >";
                };
                return f;
            },
            route: {
                /**
                 * from angular.js
                 * 
                 * @param path
                 *                {string} path
                 * @param opts
                 *                {Object} options
                 * @return {?Object}
                 * 
                 * @description Normalizes the given path, returning a regular
                 *              expression and the original path.
                 * 
                 * Inspired by pathRexp in visionmedia/express/lib/utils.js.
                 */
                pathRegExp: function (path, opts) {
                    var insensitive = opts.caseInsensitiveMatch, ret = {
                        originalPath: path,
                        regexp: path
                    }, keys = ret.keys = [];

                    path = path.replace(/([().])/g, '\\$1').replace(
                            /(\/)?:(\w+)([\?|\*])?/g,
                            function (_, slash, key, option) {
                                var optional = option === '?' ? option : null;
                                var star = option === '*' ? option : null;
                                keys.push({
                                    name: key,
                                    optional: !!optional
                                });
                                slash = slash || '';
                                return '' + (optional ? '' : slash) + '(?:'
                                        + (optional ? slash : '')
                                        + (star && '(.+?)' || '([^/]+)')
                                        + (optional || '') + ')'
                                        + (optional || '');
                            }).replace(/([\/$\*])/g, '\\$1');

                    ret.regexp = new RegExp('^' + path + '$', insensitive ? 'i'
                            : '');
                    return ret;
                }
            },
            // cross browser setimeout (mutiple args)
            setTimeout: function (callback, timeout) {
                var args = Array.prototype.slice.call(arguments); // Convert
                // args to
                // array
                args = args.slice(2); // tricks: Convert args to array
                var f = function () {
                    callback.apply(null, args);
                }; // Makes 'this' to be mapped to DOMWindow
                return window.setTimeout(f, timeout); // Return timeout ID
            },
            clearTimeout: function (timeoutId) {
                window.clearTimeout(timeoutId);
            },
            encodeQueryData: function (data) {
                var ret = [];
                for (var d in data) {
                    if (!d)
                        continue;
                    ret.push(encodeURIComponent(d) + "="
                            + encodeURIComponent(data[d]));
                }
                return ret.join("&");
            },
            urlAddParams: function (url, data) {
                var i, urlp, queryp = '', anchorp = '';
                i = url.lastIndexOf("#");
                if (i >= 0) {
                    url = url.substring(0, i);
                    anchorp = url.substring(i);
                }
                i = url.indexOf("?");
                if (i >= 0) {
                    urlp = url.substring(0, i);
                    queryp = url.substring(i + 1);
                }
                else
                    urlp = url;
                // merge with url data data
                var pair, vars = queryp.split("&");
                for (i = 0; i < vars.length; i++) {
                    pair = vars[i].split("=");
                    if (typeof data[pair[0]] === "undefined") {
                        if (typeof pair[1] === "undefined")
                            data[pair[0]] = '';
                        else
                            data[pair[0]] = decodeURIComponent(pair[1]);
                    }
                }
                var query = $4p.encodeQueryData(data);
                if (query)
                    query = '?' + query;
                return urlp + query + anchorp;
            },
            cookie: function (key, value, options) {
                // key and value given, set cookie...
                if (arguments.length > 1
                        && (value === null || typeof value !== "object")) {
                    options = jQuery.extend({}, options);

                    if (value === null)
                        options.expires = -1;

                    if (typeof options.expires === 'number') {
                        var days = options.expires, t = options.expires = new Date();
                        t.setDate(t.getDate() + days);
                    }
                    return (document.cookie = [
                        encodeURIComponent(key),
                        '=',
                        options.raw ? String(value)
                                : encodeURIComponent(String(value)),
                        options.expires ? '; expires='
                                + options.expires.toUTCString() : '', // use
                        // expires
                        // attribute,
                        // max-age
                        // is
                        // not
                        // supported
                        // by
                        // IE
                        options.path ? '; path=' + options.path : '',
                        options.domain ? '; domain=' + options.domain : '',
                        options.secure ? '; secure' : ''].join(''));
                }
                // key and possibly options given, get cookie...
                options = value || {};
                var result, decode = options.raw ? function (s) {
                    return s;
                } : decodeURIComponent;
                return (result = new RegExp('(?:^|; )'
                        + encodeURIComponent(key) + '=([^;]*)')
                        .exec(document.cookie)) ? decode(result[1]) : null;
            },
            throbberCounter: 0,
            rpcWait: function (start) {
                $4p.throbber(start);
            },
            throbber: function (start) {
                if ((!$4p.throbberEl) && ($4p.cfg.throbber.enabled)) {
                    if (!$4p.cfg.throbber['class'])
                        $4p.cfg.throbber['class'] = 'ui-state-highlight ui-corner-all';
                    if (!$4p.cfg.throbber['style'])
                        $4p.cfg.throbber['style'] = 'position:fixed;top:0px;right:0px;margin:6px;padding:6px;z-index:1000;display:none;';
                    if (!$4p.cfg.throbber['img'])
                        $4p.cfg.throbber['img'] = $4p.glob('url_static_core')
                                + '/4p/styles/img/ajax-loader.gif';
                    $4p.throbberEl = $(
                            '<div id="fp-throbber" class="'
                            + $4p.cfg.throbber['class'] + '" style="'
                            + $4p.cfg.throbber['style'] + '" />')
                            .append(
                                    $('<img style="vertical-align:middle;" src="'
                                            + $4p.cfg.throbber['img'] + '" />'))
                            .append(
                                    $('<span style="padding:5px;" />').html(
                                    'traitement en cours')).hide()
                            .appendTo('body');
                }

                if (start) {
                    $4p.throbberCounter++;
                    if ($4p.throbberCounter == 1) {
                        if ($4p.cfg.throbber.enabled) {
                            $('#fp-throbber').show();
                            // on garde au premier plan
                            var t = null;
                            var mz = function () {
                                t = null;
                                var z = $4p.maxZIndex($('body'));
                                if (parseInt($('#fp-throbber').css('z-index')) < z) {
                                    t = setTimeout(mz, 100);
                                    $('#fp-throbber').css('z-index', z + 1);
                                }
                            };
                            mz();
                        }
                        $($4p.events).trigger('fp.throbber.start');
                    }
                } else {
                    $4p.throbberCounter = $4p.throbberCounter - 1;
                    if ($4p.throbberCounter < 0)
                        $4p.throbberCounter = 0;
                    if (!$4p.throbberCounter) {
                        if ($4p.cfg.throbber.enabled)
                            $('#fp-throbber').hide();
                        $($4p.events).trigger('fp.throbber.stop');
                    }
                }
            },
            cssLoad: function (url, media) {
                var media = (media) ? ' media="' + media + '" '
                        : ' media="all"  ';
                var u = url + $4p.skipCache();
                if ($('[href="' + u + '"]').length)
                    return true;
                $("head").append(
                        $("<link rel='stylesheet' href='" + u
                                + "' type='text/css' />"));
            },
            cacheScript: [],
            headScriptLoad: function (url) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                $("head").append(script);
            },
            scriptLoad: function (url, callback) {
                var u = url + $4p.skipCache();
                if ($('[src="' + u + '"]').length)
                    return true;

                // fake script for lazy loading
                if ($('meta[content="' + u + '"]', $("head")).length)
                    return true;
                var meta = document.createElement('meta');
                meta.content = url;
                meta.name = 'script';
                $("head").append(meta);

                if (typeof $4p.cacheScript[url] != 'undefined') {
                    if (typeof callback == 'function')
                        callback();
                    return true;
                }
                return $.ajax({
                    cache: true,
                    url: u,
                    dataType: 'script',
                    async: false,
                    success: function (script) {
                        $4p.cacheScript[url] = 1;
                        try {
                            if (typeof callback == 'function')
                                callback();
                        } catch (error) {
                            $4p.log(error);
                        }
                    }
                });
            },
            batchJsonRpc: function (url, params) {
                var options = {
                    delay: 0
                };
                var t = {
                    batch: {},
                    callback: null
                };
                t.options = jQuery.extend(options, params);

                t.addCallback = function (cb) {
                    t.callback = cb;
                    return t;
                };
                t.add = function (method, params) {
                    var req = {};
                    req.addCallback = function (cb) {
                        req.callback = cb;
                        return req;
                    };
                    req.callback = null;
                    req.data = {
                        method: method,
                        params: params,
                        id: (new Date().getTime() * 1000)
                                + Math.floor(Math.random() * 1000)
                    };
                    req.doCallback = function (r) {
                        if (typeof (r.error) == 'undefined')
                            req.error = null;
                        if (typeof (r.result) == 'undefined')
                            req.result = null;
                        if (typeof (r.id) == 'undefined')
                            req.id = null;
                        if (typeof r.callback == 'function')
                            req.callback(r);
                    };
                    t.batch[req.data.id] = req;
                    return req;
                };
                t.call = function (method, params) {
                    if (t.options.delay) {
                        if (t.timeout)
                            clearTimeout(t.timeout);
                        var c = function () {
                            t.execCall(method, params);
                        };
                        t.timeout = setTimeout(c, t.options.delay);
                    } else {
                        t.execCall(method, params);
                    }
                };
                t.execCall = function (method, params) {
                    var b = {}, x, i = 0;
                    for (x in t.batch) {
                        b[i++] = t.batch[x].data;
                    }
                    // batch is empty
                    if (!i) {
                        if (typeof t.callback == 'function')
                            t.callback();
                        return;
                    }
                    $.ajax({
                        type: "POST",
                        url: $4p.urlAddParams(url, {
                            "method": 'batch'
                        }),
                        data: b,
                        cache: false,
                        async: true,
                        dataType: "json",
                        success: function (r, textStatus,
                                XMLHttpRequest) {
                            if (Object.prototype.toString.call(r) === '[object Array]') {
                                var x, br;
                                for (x in r) {
                                    if (r[x].id) {
                                        if (t.batch[r[x].id]) {
                                            // mix request params and
                                            // result
                                            br = t.batch[r[x].id];
                                            br.result = r[x].result;
                                            br.error = r[x].error;
                                            br.id = r[x].id;
                                            br.doCallback(br);
                                            // clear batch queue
                                            delete t.batch[r[x].id];
                                        }
                                    }
                                }
                            }
                            if (typeof t.callback == 'function')
                                t.callback(r);
                        },
                        error: function (XMLHttpRequest, textStatus,
                                errorThrown) {
                            var error = textStatus + ': ' + errorThrown;
                            if (typeof r == 'undefined') {
                                $4p.log(error);
                                var r = {
                                    error: error
                                };
                            } else if (!r.error)
                                r.error = error;
                            if (typeof t.callback == 'function')
                                t.callback(r);
                        }
                    });
                };
                return t;
            },
            jsonRpc: function (url) {
                var t = {};
                t.callSync = function (method, params) {
                    var d = new Date();
                    var id = d.getTime();
                    var r = {
                        'error': null,
                        'result': null,
                        'id': null
                    };
                    var data = {
                        "method": method,
                        "params": params,
                        "id": id
                    };
                    $.ajax({
                        type: "POST",
                        url: $4p.urlAddParams(url, {
                            "method": method
                        }),
                        data: data,
                        cache: false,
                        async: false,
                        dataType: "json",
                        success: function (resp, textStatus, XMLHttpRequest) {
                            r = resp;
                        },
                        error: function (XMLHttpRequest, textStatus,
                                errorThrown) {
                            r = {
                                'error': textStatus,
                                'result': null,
                                'id': null
                            };
                        }
                    });
                    if (r == null)
                        r = {
                            'error': null,
                            'result': null,
                            'id': null
                        };
                    else {
                        if (typeof (r.error) == 'undefined')
                            r.error = null;
                        if (typeof (r.result) == 'undefined')
                            r.result = null;
                        if (typeof (r.id) == 'undefined')
                            r.id = null;
                    }
                    return r;
                };
                t.call = function (method, params) {
                    var req = {};
                    req.addCallback = function (cb) {
                        req.callback = cb;
                        return req;
                    };
                    req.callback = null;
                    req.data = {
                        method: method,
                        params: params,
                        id: new Date().getTime()
                    };
                    req.error = req.result = req.process = null;
                    req.doCallback = function (r) {
                        if (typeof (r.error) == 'undefined')
                            req.error = null;
                        if (typeof (r.result) == 'undefined')
                            req.result = null;
                        if (typeof (r.id) == 'undefined')
                            req.id = null;
                        if (typeof r.callback == 'function')
                            req.callback(req);
                    };

                    req.process = $.ajax({
                        type: "POST",
                        url: $4p.urlAddParams(url, {
                            "method": method
                        }),
                        data: req.data,
                        cache: false,
                        async: true,
                        dataType: "json",
                        success: function (r, textStatus, XMLHttpRequest) {
                            req.result = r.result;
                            req.error = r.error;
                            req.id = r.id;
                            req.doCallback(req);
                        },
                        error: function (XMLHttpRequest, textStatus,
                                errorThrown) {
                            req.result = null;
                            req.error = textStatus + ': ' + errorThrown;
                            req.id = req.id;
                            req.doCallback(req);
                        }
                    });
                    return req;
                };
                return t;
            },
            redirect: function (url) {
                var url = '<script type="text/javascript">top.location.href = \''
                        + url + '\';</script>';
                $('body').html(url);
            },
            // toujours placer au-dessus
            maxZIndex: function ($context) {
                return Math.max.apply(null, $.map($context.find('div'),
                        function (e) {
                            return parseInt($(e).css('z-index')) || 1;
                        }));
            },
            /* deprecated */
            msg: function (title, msg) {
                var d = {
                    title: title,
                    msg: msg
                };
                var r = $4p.tpl(
                        '<div title="~#= title #~"><p>~#= msg #~</p></div>')
                        .render(d);
                $(r).dialog({
                    modal: true,
                    height: Math.round(screen.height * 0.3),
                    width: Math.round(screen.width * 0.35),
                    buttons: {
                        Fermer: function () {
                            $(this).dialog('close');
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("destroy");
                        $(this).remove();
                    }
                });
            },
            /* deprecated */
            msgPrompt: function (title, msg, data) {
                var $dialog = $('<div />').attr('title', title);
                var $input = $('<input type="text" />').val(data);
                $('<p />').html(msg).appendTo($dialog);
                $('<p />').append($input).appendTo($dialog);
                $dialog.dialog({
                    modal: true,
                    open: function () {
                        $(this).data(data);
                        if (data && data.value) {
                            $input.val(data.value);
                        }
                    },
                    buttons: {
                        valider: function () {
                            $(this).data('value', $input.val());
                            if (typeof $(this).data('callback') == 'function') {
                                $(this).data('callback')($(this).data('value'),
                                        $(this).data());
                            }
                            $(this).dialog('close');
                        },
                        annuler: function () {
                            $(this).data('value', false);
                            $(this).dialog('close');
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("destroy");
                        $(this).remove();
                    }
                });
                $dialog.addCallback = function (cb) {
                    $dialog.data("callback", cb);
                };
                return $dialog;
            },
            /* deprecated */
            msgConfirm: function (title, msg, data) {
                var d = {
                    title: title,
                    msg: msg
                };
                var r = $4p.tpl(
                        '<div title="~#= title #~"><p>~#= msg #~</p></div>')
                        .render(d);
                var dial = $(r)
                        .dialog(
                                {
                                    modal: true,
                                    height: Math.round(screen.height * 0.3),
                                    width: Math.round(screen.width * 0.35),
                                    open: function () {
                                        $(this).data(data);
                                    },
                                    buttons: {
                                        non: function () {
                                            $(this).data('value', false);
                                            $(this).dialog('close');
                                        },
                                        oui: function () {
                                            $(this).data('value', true);
                                            $(this).dialog('close');
                                        }
                                    },
                                    close: function (event, ui) {
                                        $(this).dialog("destroy");
                                        if (typeof $(this).data('callback') == 'function') {
                                            $(this).data('callback')(
                                                    $(this).data('value'),
                                                    $(this).data());
                                        }
                                        $(this).remove();
                                    }
                                });
                dial.addCallback = function (cb) {
                    $(dial).data("callback", cb);
                };
                return dial;
            },
            /* deprecated */
            msgError: function (title, msg) {
                var d = {
                    title: title,
                    msg: msg
                };
                var s = '<div class="ui-state-error ui-corner-all" title="~#= title #~"><div style="padding: 0.8em" class="ui-state-error ui-corner-all"><p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>~#= msg #~</p></div></div>';
                var r = $4p.tpl(s).render(d);
                $(r).dialog({
                    modal: false,
                    height: Math.round(screen.height * 0.3),
                    width: Math.round(screen.width * 0.35),
                    buttons: {
                        Fermer: function () {
                            $(this).dialog('close');
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("destroy");
                        $(this).remove();
                    }
                });
            },
            base64_encode: function (data) {
                // http://kevin.vanzonneveld.net
                // + original by: Tyler Akins (http://rumkin.com)
                // + improved by: Bayron Guevara
                // + improved by: Thunder.m
                // + improved by: Kevin van Zonneveld
                // (http://kevin.vanzonneveld.net)
                // + bugfixed by: Pellentesque Malesuada
                // + improved by: Kevin van Zonneveld
                // (http://kevin.vanzonneveld.net)
                // + improved by: Rafał Kukawski (http://kukawski.pl)
                // * example 1: base64_encode('Kevin van Zonneveld');
                // * returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
                // mozilla has this native
                // - but breaks in 2.0.0.12!
                // if (typeof this.window['btoa'] == 'function') {
                // return btoa(data);
                // }
                var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, enc = "", tmp_arr = [];

                if (!data) {
                    return data;
                }

                do { // pack three octets into four hexets
                    o1 = data.charCodeAt(i++);
                    o2 = data.charCodeAt(i++);
                    o3 = data.charCodeAt(i++);

                    bits = o1 << 16 | o2 << 8 | o3;

                    h1 = bits >> 18 & 0x3f;
                    h2 = bits >> 12 & 0x3f;
                    h3 = bits >> 6 & 0x3f;
                    h4 = bits & 0x3f;

                    // use hexets to index into b64, and append result to
                    // encoded string
                    tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2)
                            + b64.charAt(h3) + b64.charAt(h4);
                } while (i < data.length);

                enc = tmp_arr.join('');
                var r = data.length % 3;
                return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
            },
            base64_decode: function (data) {
                // http://kevin.vanzonneveld.net
                // + original by: Tyler Akins (http://rumkin.com)
                // + improved by: Thunder.m
                // + input by: Aman Gupta
                // + improved by: Kevin van Zonneveld
                // (http://kevin.vanzonneveld.net)
                // + bugfixed by: Onno Marsman
                // + bugfixed by: Pellentesque Malesuada
                // + improved by: Kevin van Zonneveld
                // (http://kevin.vanzonneveld.net)
                // + input by: Brett Zamir (http://brett-zamir.me)
                // + bugfixed by: Kevin van Zonneveld
                // (http://kevin.vanzonneveld.net)
                // * example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
                // * returns 1: 'Kevin van Zonneveld'
                // mozilla has this native
                // - but breaks in 2.0.0.12!
                // if (typeof this.window['atob'] == 'function') {
                // return atob(data);
                // }
                var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, dec = "", tmp_arr = [];

                if (!data) {
                    return data;
                }
                data += '';
                do { // unpack four hexets into three octets using index
                    // points in b64
                    h1 = b64.indexOf(data.charAt(i++));
                    h2 = b64.indexOf(data.charAt(i++));
                    h3 = b64.indexOf(data.charAt(i++));
                    h4 = b64.indexOf(data.charAt(i++));

                    bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

                    o1 = bits >> 16 & 0xff;
                    o2 = bits >> 8 & 0xff;
                    o3 = bits & 0xff;

                    if (h3 == 64) {
                        tmp_arr[ac++] = String.fromCharCode(o1);
                    } else if (h4 == 64) {
                        tmp_arr[ac++] = String.fromCharCode(o1, o2);
                    } else {
                        tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
                    }
                } while (i < data.length);

                dec = tmp_arr.join('');

                return dec;
            },
            // https://developer.mozilla.org/en-US/docs/DOM/window.btoa
            // must be the same of php Filter::b64WebEncode
            // note: there no equivalent in php of escape/unescape() javascript
            // function
            b64WebEncode: function (str) {
                str = encodeURIComponent(str);
                if (typeof window.btoa == 'function') {
                    str = window.btoa(str);
                } else
                    str = $4p.base64_encode(str);
                str = str.replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g,
                        ",");
                return str;
            },
            // must be the same of php Filter::b64WebDecode
            b64WebDecode: function (str) {
                str = str.replace(/-/g, "+").replace(/_/g, "/").replace(/,/g,
                        "=");
                if (typeof window.atob == 'function')
                    str = window.atob(str);
                else
                    str = $4p.base64_decode(str);
                return decodeURIComponent(str);
            },
            dateFromMysqlDate: function (strTime) {
                if (!strTime)
                    return null;
                if (typeof strTime == 'object')
                    return strTime;
                var t = strTime.split(/[- :]/);
                if (t.length >= 3) {
                    return new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
                }
                return null;
            },
            dateToMysqlDate: function (dateObj) {
                return dateObj.getFullYear() + '-' +
                        (dateObj.getMonth() < 9 ? '0' : '') + (dateObj.getMonth() + 1) + '-' +
                        (dateObj.getDate() < 10 ? '0' : '') + dateObj.getDate();
            }
        };
    })(jQuery);


    $4p.template = function (tpl) {
        var f = {};
        if (typeof tpl == 'string') {
            if (tpl.substring(0, 4) == 'http') {
                $.ajax({
                    cache: true,
                    url: tpl + $4p.skipCache(),
                    dataType: 'text',
                    async: false,
                    success: function (d) {
                        f.tpl = d;
                    }
                });
            } else
                f.tpl = tpl;

        } else {
            $4p.log('unknow tpl type ' + typeof tpl);
            return;
        }
        // test scope part
        f.scope = {};
        f.element = document.createElement('div');
        f.element.innerHTML = f.tpl;
        f.element = $(f.element);
        var $textarea = $('<textarea />');
        f.element.find('[data-fp-scope]').each(
                function () {
                    f.scope[$(this).attr('data-fp-scope')] = $textarea.append($(this)).html();
                    if ($(this)[0].hasAttribute('data-fp-remove'))
                        $(this).remove();
                });
        f.scope['root'] = $textarea.append(f.element).html();

        f.cache = {};
        f.render = function (data, scope, data_key) {
            var err = "", func;
            if (!scope)
                scope = 'root';
            if (!data_key)
                data_key = 'data';
            var tpl_data = {};
            tpl_data[data_key] = new $4p.templateData(data);
            try {
                if (typeof f.cache[scope] != 'function') {
                    var strFunc = "var p=[]; var print = function(str) { p.push(str); }; p.push('"
                            + f.scope[scope].replace(/[\r\t\n]/g, " ")
                            .split("'")
                            .join("\\'")
                            .replace(/<script>/g, "{{")
                            .replace(/<\/script>/g, "}}")
                            .replace(/{{=([^}{2}]+)}}/g, function (m, p1) {
                                return "'+" + p1.split("\\'").join("'") + "+'";
                            })
                            .replace(/{{(.+?)}}/g, function (m, p1) {
                                return "');" + p1.split("\\'").join("'") + ";p.push('";
                            })
                            + "');return p.join('');";

                    var args = [];
                    for (var x in tpl_data) {
                        args.push(x);
                    }
                    f.cache[scope] = new Function(args, strFunc);
                }

                var args_value = [];
                for (var x in tpl_data) {
                    args_value.push(tpl_data[x]);
                }

                return f.cache[scope].apply(this, args_value);
            } catch (e) {
                err = e.message;
            }
            return "< # ERROR: " + err + ' scope:' + scope + "-- ok # >";
        };
        return f;

    };

    $4p.templateData = function (vars, key) {
        this.vars = null;
        this.key = null;
        this.i_iterate = null;
        this.i_total = null;
        this.i_position = null;
        this.instanceOfTemplateData = true;

        this.constructor = function (vars, key) {
            var k, v;
            this.key = (key) ? key : null;
            
            if (vars) {
                if (vars.instanceOfTemplateData === true) {
                    this.vars = vars.vars;
                } else if ((/boolean|number|string/).test(typeof vars)) {
                    this.vars = vars;
                } else {
                    this.vars = {};
                    for (k in vars) {
                        if (vars.hasOwnProperty(k)) {
                            v = vars[k];
                            this.vars[k] = new $4p.templateData(v, k);
                            // reference the data index
                            if (!this[k])
                                this[k] = this.vars[k];
                        }
                    }
                }
            }
        };
        this.constructor.call(this, vars, key);

        this.is = function (args) {
            if (!(args instanceof Array))
                args = Array.prototype.slice.call(arguments);
            var o = this;
            var v;
            for (k in args) {
                if (args.hasOwnProperty(k)) {
                    v = args[k];
                    if (o && (typeof o.vars[v] != 'undefined')
                            && (o.vars[v].instanceOfTemplateData)) {
                        o = o.vars[v];
                    } else {
                        return new $4p.templateData('');
                    }
                }
            }
            return o;
        };

        this.value = function () {
            return this.vars;
        };
        this.v = function () {
            return this.value();
        };
        this.e = function () {
            return this.value();
        };

        this.toString = function () {
            return this.value() + '';
        };

        this.valueOf = function () {
            return this.value() + '';
        };

        /*
         * ITERATOR
         */       
        if ( this.vars !== null && typeof this.vars === 'object') {
            this.currentKey = 0;
            if (!this.vars.instanceOfTemplateData) {
                this.keys = [];
                for (var key in this.vars) {
                    if (this.vars.hasOwnProperty(key)) {
                        this.keys.push(key);
                    }
                }
            } else {
                this.keys = this.vars.keys;
            }

            this.end = function () {
                this.currentKey = (this.keys.length - 1);
            };
            this.reset = function () {
                this.currentKey = 0;
            };
            this.prev = function () {
                return this.vars[this.keys[--this.currentKey]];
            };
            this.next = function () {
                return this.vars[this.keys[++this.currentKey]];
            };
            this.count = function () {
                return this.keys.length;
            };
            this.current = function () {
                return this.vars[this.keys[this.currentKey]];
            };
            this.iteratePosition = function () {
                if (this.i_position === null)
                    return this.i_position = this.i_total - this.i_iterate;
                return this.i_position;
            };

            this.iterate = function (nb, offset, rewind) {
                var nb = (nb) ? nb : null;
                var offset = (offset) ? offset : null;
                var rewind = (rewind) ? rewind : false;

                if (!this.instanceOfTemplateData)
                    return null;
                if ((/boolean|number|string/).test(typeof this.vars))
                    return null;
                if (this.i_iterate == null) {
                    if (rewind) {
                        this.end();
                    } else {
                        this.reset();
                    }
                    if (offset) {
                        if (offset > this.count()) {
                            return null;
                        } else {
                            for (var i = 0; i != offset; i++) {
                                if (rewind)
                                    this.prev();
                                else
                                    this.next();
                            }
                        }
                    }
                    if (nb === null) {
                        this.i_total = this.count();
                        this.i_iterate = this.i_total + 1;
                    } else {
                        this.i_iterate = nb + 1;
                        this.i_total = nb;
                    }
                }
                this.i_iterate--;
                this.i_position = null;
                var a;
                if (this.i_iterate > 0 && (a = this.current())) {
                    if (!rewind)
                        this.next();
                    else
                        this.prev();
                    a.i_position = this.iteratePosition();
                    return a;
                }
                this.i_total = this.i_iterate = null;
                this.reset();
            };
        }
    };
}
;



jQuery(function () {
    jQuery('body').data('4p', $4p);
});

