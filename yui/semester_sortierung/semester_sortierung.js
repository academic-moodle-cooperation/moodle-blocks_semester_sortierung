var temptest;
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

        var self = this;
        Y.delegate('click', function(e){self.ajaxLoad(e);}, node.one('#semesteroverviewcontainer'), '.semestersortierung .expand_button');
        Y.delegate('click', function(e){self.setNewStatus(e);}, node.one('#semesteroverviewcontainer'), 'fieldset legend');
    },

    setNewStatus : function(e) {

        var stat = e.currentTarget.next().getStyle('overflow') == 'visible' ? '1':'0';
        var params = {
            id : e.currentTarget.getAttribute('id'),
            state: stat,
        };
        Y.io(M.cfg.wwwroot+'/blocks/semester_sortierung/ajax_setstate.php', {
                method:'GET',
                data:  build_querystring(params),
                context:this
            });
    },

    ajaxLoad : function(e) {
        var courseid = new String(e.currentTarget);
        courseid = courseid.substring(8, courseid.indexOf(' '));
        var targetDiv = Y.one('#sbox' + courseid)._node;
        togglesemesterbox(courseid);
        if (targetDiv.innerHTML == '') {
            var params = {
                cid : courseid
            };
            Y.one('#imgbox' + courseid)._node.className = 'loading';

            Y.io(M.cfg.wwwroot+'/blocks/semester_sortierung/ajax_modinfo.php', {
                method:'GET',
                data:  build_querystring(params),
                on: {
                    complete: this.ajaxProcessResponse
                },
                context:this
            });
        }
    },

    ajaxProcessResponse : function(tid, outcome) {
        var response = outcome.responseText;
        var delim_pos = response.indexOf('***');
        var courseid = response.substring(0, delim_pos);
        response = response.substring(delim_pos+3);
        var targetDiv = Y.one('#sbox' + courseid)._node;
        targetDiv.innerHTML = response;
        Y.one('#imgbox' + courseid).setAttribute('class', 'minus');
        //togglesemesterbox(courseid);
        //self.setNewStatus(e);
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

function togglesemesterbox(boxid) {
    var mybox = document.getElementById("sbox" + boxid);
    var imgbox = document.getElementById("imgbox" + boxid);
    var vis = 0;
    if (mybox.style.overflow == "visible") {
        mybox.style.overflow = "hidden";
        mybox.style.height = "1px";
        imgbox.className = "plus";
    }
    else {
        mybox.style.overflow = "visible";
        mybox.style.height = "";
        imgbox.className = "minus";
        vis = 1;
    }
}