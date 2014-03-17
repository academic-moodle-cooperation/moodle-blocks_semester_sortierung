YUI.add('moodle-block_semester_sortierung-semester_sortierung', function(Y){
var SEMSORT = function(config) {
    SEMSORT.superclass.constructor.apply(this, arguments);
}
SEMSORT.prototype = {
    /**
     * semsort id(block instance id)
     */
    id : null,
    /**
     * Initialise the tree object when its first created.
     */
    initializer : function(config) {
        this.id = config.id;
        var node = Y.one('#inst'+config.id);
        if (Y.one('.no_javascript')) {
            Y.one('.no_javascript').removeClass('no_javascript');
        }
        Y.all('#semesteroverviewcontainer fieldset .togglefavorites').set('href', 'javascript: void(0);');
        var self = this;
        Y.delegate('click', function(e){self.setNewStatus(e);}, node.one('#semesteroverviewcontainer'), 'fieldset legend');
        Y.delegate('click', function(e){self.toggleFavorites(e);}, node.one('#semesteroverviewcontainer'), 'fieldset .togglefavorites');
    },
    
    toggleFavorites : function(e) {
        var target = e.currentTarget.ancestor();
        var cid = target.getData('id');
        var stat = '0';
        if (target.getData('fav') == '0') {
            target.one('.togglefavorites.on').removeClass('invisible');
            target.one('.togglefavorites.off').addClass('invisible');
            target.setData('fav', '1');
            var newtarget = target.cloneNode(true);
            newtarget.setData('fav', 1);
            Y.one('#semesteroverviewcontainer fieldset.fav').insert(newtarget);
            stat = '1';
            this.sortFavorites();
            Y.one('#semesteroverviewcontainer fieldset.fav').removeClass('empty');
        } else {
            Y.one('#semesteroverviewcontainer fieldset.fav').all('fieldset.course').each(function(e) {
                    if (e.getData('id') == cid) {
                        e.remove();
                    }
                });
            Y.one('#semesteroverviewcontainer').all('fieldset.course').each(function(e) {
                    if (e.getData('id') == cid) {
                        e.one('.togglefavorites.off').removeClass('invisible');
                        e.one('.togglefavorites.on').addClass('invisible');
                        e.setData('fav', '0');
                    }
                });
            var favcount = Y.one('#semesteroverviewcontainer fieldset.fav').all('fieldset.course')._nodes.length;
            if (favcount <= 0) {
                Y.one('#semesteroverviewcontainer fieldset.fav').addClass('empty');
            }
        }
        var params = {
            id: cid,
            status: stat
        };
        Y.io(M.cfg.wwwroot+'/blocks/semester_sortierung/ajax_favorites.php', {
            method:'GET',
            data:  build_querystring(params),
            context:this
        });
    },
    
    sortFavorites : function() {
        var favs = Y.one('#semesteroverviewcontainer fieldset.fav');
        var nothidden = favs.all('fieldset.course.nothidden')._nodes;
        var hidden = favs.all('fieldset.course.hidden')._nodes;
        nothidden.sort(this.customSorting);
        hidden.sort(this.customSorting);
        favs.all('fieldset.course').each(function(e){e.remove();});
        favs = Y.one('#semesteroverviewcontainer fieldset.fav .expandablebox');
        for (var i = 0; i < nothidden.length; i++) {
            favs.appendChild(nothidden[i]);
        }
        for (var i = 0; i < hidden.length; i++) {
            favs.appendChild(hidden[i]);
        }
    },
    
    customSorting : function(a, b) {
        var a = Y.one(a).one('legend .courselink').getHTML().toLowerCase();
        var b = Y.one(b).one('legend .courselink').getHTML().toLowerCase();
        return a < b ? -1 : b < a ? 1 : 0;
    },

    setNewStatus : function(e) {
        if (e.target.hasClass('courselink')) {
            return; //don't do anything when a link is pressed 
        }
        var fldset = e.currentTarget.ancestor();
        fldset.toggleClass('expanded');
        
        var btype = fldset.hasClass('semester') ? 's' : 'c';
        var bstate = fldset.hasClass('expanded') ? 1 : 0;
        var targetdiv = fldset.one('.expandablebox');
        var useajax = btype == 'c' && targetdiv.getHTML() == ''  && bstate == 1 ? 1 : 0;
        
        var params = {
            id: fldset.getData('id'),
            state: bstate,
            boxtype: btype,
            ajax: useajax
        };
        if (useajax) {
            fldset.addClass('loading');
        }
        Y.io(M.cfg.wwwroot+'/blocks/semester_sortierung/ajax_setstate.php', {
                method:'GET',
                data:  build_querystring(params),
                context:this,                
                on: {
                    complete: function(t, outcome) {
                        if (useajax == 1) {
                            fldset.removeClass('loading');
                            targetdiv.setHTML(outcome.responseText);
                        }
                    }
                }
            });
    }
}
// The tree extends the YUI base foundation.
Y.extend(SEMSORT, Y.Base, SEMSORT.prototype, {
    NAME : 'semester_sortierung-semsort',
    ATTRS : {
        instance : {
            value : null
        }
    }
});

/**
 * This namespace will contain all of the contents of the navigation_plus blocks
 * global navigation_plus and settings.
 * @namespace
 */
M.block_semester_sortierung = M.block_semester_sortierung || {
    /** The number of expandable branches in existence */
    instance : null,
    /**
     * Add new instance of navigation_plus tree to tree collection
     */
    init_add_semsort:function(properties) {
        if (M.core_dock) {
            M.core_dock.init(Y);
        }
        new SEMSORT(properties);
    }
};

}, '@VERSION@', {requires:['base', 'core_dock', 'io-base', 'node', 'node-base','dom', 'event-custom', 'event-delegate', 'json-parse']});
