(function() {
    "use strict";

    function HomepageController ($http, CONFIG) {

        this.room = 0;

        this.create = function() {
            $http.post(CONFIG.ROUTING.ROOM.CREATE).success(function(data) {
                if(data.hash.length) {
                    window.location.href = CONFIG.ROUTING.ROOM.ENTER.replace('_HASH_', data.hash);
                }
            });
        };
    };

    angular.module('potionmaker').controller('HomepageController', HomepageController);
})();