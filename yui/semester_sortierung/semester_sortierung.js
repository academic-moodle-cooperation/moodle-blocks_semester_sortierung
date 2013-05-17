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
        Y.one('.no_javascript').removeClass('no_javascript');
        var self = this;
        Y.delegate('click', function(e){self.setNewStatus(e);}, node.one('#semesteroverviewcontainer'), 'fieldset legend');
    },

    setNewStatus : function(e) {
        if (e.target.hasClass('courselink')) {
            return; //don't do anything when a link is pressed 
        }
        var fldset = e.currentTarget.ancestor();
        fldset.toggleClass('expanded');
        
        var btype = fldset.hasClass('semester') ? 's' : 'c';
        var bstate = fldset.hasClass('expanded') ? 1 : 0;
        var targetdiv = e.currentTarget.next();
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
                },
            });
    },
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
