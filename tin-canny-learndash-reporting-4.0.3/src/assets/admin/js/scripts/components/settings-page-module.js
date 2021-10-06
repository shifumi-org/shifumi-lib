var settingsPageModule = {

    elFeatureButton: jQuery('.uo_feature_button'),

    switchOnOff: function () {

        var moduleSwitch = jQuery(this);

        moduleSwitch.toggleClass("uo_feature_activated uo_feature_deactivated");

        if (moduleSwitch.hasClass('uo_feature_activated')) {
            // WP-API activate module
        }

        if (moduleSwitch.hasClass('uo_feature_deactivated')) {
            // WP-API de-activate module
        }
    },

    addSwitchEvents: function () {
        this.elFeatureButton.on('click', this.switchOnOff);
    },

    removeSwitchEvents: function () {
        this.elFeatureButton.off('click', this.switchOnOff);
    }

};
