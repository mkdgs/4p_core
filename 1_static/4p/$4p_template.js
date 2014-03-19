/*
* 4p javascript template ports 
* version : 0.3
* Copyright Desgranges Mickael
* mickael@mkdgs.fr
* 
* note : 
* - commentaire javascript sur une ligne non supporté
* - mettre des commentaire html autour des instructions pour quels ne soient pas éffacer par le DOM (ex code entre <table> et <tr>) 
* 
* todo:
* - prefixer les nom de propriété dans template_data pour éviter des soucis de collisions
*/
$4p.template = function(tpl) {
    var f = {};
    if (typeof tpl == 'string') {
        if (tpl.substring(0, 4) == 'http') {
            $.ajax({
                cache : true,
                url : tpl + $4p.skipCache(),
                dataType : 'text',
                async : false,
                success : function(d) {
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
    f.scope = {
        root : f.tpl
    };
    
    f.element = document.createElement('div');
    f.element.innerHTML = f.tpl;
    f.element = $(f.element);
    f.element.find('[data-fp-scope]').each(
            function() {
                f.scope[$(this).attr('data-fp-scope')] = $('<textarea />').append(
                        $(this)).html();
            });

    f.cache = {};
    
    f.render = function(data, scope, data_key) {
        var err = "", func;
        if (!scope)    scope = 'root';
        if (!data_key) data_key = 'data';
        var tpl_data = {};
        tpl_data[data_key] = new $4p.templateData(data);
        try {
            if (typeof f.cache[scope] != 'function') {
                // inspired by http://www.west-wind.com/weblog/posts/509108.aspx
                // put all data in function scope
                    
                var strFunc =  "var args = arguments;" 
                +"for ( var x in  args[0] ) {"
                +"   if (args[0].hasOwnProperty(x)) eval('var ' + x + ' = args[0][x];'); "
                +"}"
                +"var p=[];var print = function(str) { p.push(str); }; p.push('"
                        + f.tpl.replace(/[\r\t\n]/g, " ")
                         .split("'")
                         .join("\\'")
                        .replace(/<script>/g, "{{")
                        .replace(/<\/script>/g, "}}")
                        .replace(/{{=([^}{2}]+)}}/g, function (m, p1) { return "'+"+p1.split("\\'").join("'")+"+'"; })
                        .replace(/{{(.+?)}}/g, function (m, p1) { return "');"+p1.split("\\'").join("'")+";p.push('"; })
                        +"');return p.join('');";
                f.cache[scope] = new Function("obj", strFunc);
            }
           
            return f.cache[scope](tpl_data);
        } catch (e) {
            err = e.message;
        }
        return "< # ERROR: " + err +" "+ scope + "-- ok # >";
    };
    
    return f;
    
};

$4p.templateData = function(vars, key) {
    this.vars = {};
    this.key = null;
    this.i_iterate = null;
    this.i_total = null;
    this.i_position = null;
    this.instanceOfTemplateData = true;
    // iterator
    this.currentKey = 0;
    this.keys = [];

    this.constructor = function(vars, key) {
        var k, v;
        this.key = (key) ? key : null;
        if ( vars == null ) {
            this.vars = vars;
        }
        else if (vars.instanceOfTemplateData) {
            this.vars = vars.vars;
            this.keys = this.vars.keys;
        } else if ((/boolean|number|string/).test(typeof vars)) {
            this.vars = vars;
        } else {
            for (k in vars) {               
                if (vars.hasOwnProperty(k)) {
                    v = vars[k];
                    this.vars[k] = new $4p.templateData(v, k);
                    this.keys.push(k);
                    // reference the data index
                    if ( !this[k] ) this[k] = this.vars[k];
                }
            }
        }
    };
    this.constructor.call(this, vars, key);

    this.is = function(args) {
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

    this.value = function() {
        return this.vars;
    };
    this.v = function() {
        return this.value();
    };
    this.e = function() {
        return this.value();
    };
    
    this.toString = function () {
      return this.value();  
    };

    // iterator
    this.end = function() {
        this.currentKey = (this.keys.length - 1);
    };
    this.reset = function() {
        this.currentKey = 0;
    };
    this.prev = function() {
        return this.vars[this.keys[--this.currentKey]];
    };
    this.next = function() {
        return this.vars[this.keys[++this.currentKey]];
    };
    this.count = function() {
        return this.keys.length;
    };
    this.current = function() {
        return this.vars[this.keys[this.currentKey]];
    };
    this.iteratePosition = function() {
        if (this.i_position === null)
            return this.i_position = this.i_total - this.i_iterate;
        return this.i_position;
    };

    this.iterate = function(nb, offset, rewind) {
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
                    for ( var i = 0; i != offset; i++) {
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

};