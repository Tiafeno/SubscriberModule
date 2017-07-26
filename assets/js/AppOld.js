(function (angular) {
    var app = angular.module('SubscribeApp', ['ngMaterial', 'ngMessages']);

    app.controller('AppCtrl', function ($scope, $http, $element) {
        this.Initialise = function () {
            $http({
                url : linksubscribe.ajax_url,
                method : "GET",
                params : {action : "getTermsCategory"}

             }).success(function(resp){
                $scope.city.push({name : 'Tous les projets', slug : 'all'});
                let city = resp;

                for(let x of city){
                    if(x.term_id == 1 || x.slug == 'all'){ continue; }

                    $scope.city.push(x);
                }

             }).error(function(){

             });
        };

        $scope.messages = {
            fr : {
                warn : {
                    msg : "Une erreur s’est produite lors de l’envoi de votre message. Veuillez essayer à nouveau plus tard.", show : 0
                },
                exist : {
                    msg : "Cette adress e-mail est déja abonnée", show : 0
                },
                success : {
                    msg : "Merci pour votre abonnement. Il a bien été envoyé.", show : 0
                }
            }
        };

        $scope.city = [];
        $scope.progressbar = false;
        $scope.subscrib_key = 'subscrib'
        $scope.subscriber = {
            clientName : null,
            clientEmail : null,
            selectedCity : []
        };

        this.Initialise();

        $scope.checkMailExist = function(){
            $http({
                url : linksubscribe.ajax_url,
                method : "POST",
                params : {
                    action : "action_ajax_check_mail_exist",
                    email : $scope.subscriber.clientEmail
                }

             }).success(function(resp){
                 if(1 == parseInt(resp)){
                     $scope.messages.fr.exist.show = 1;
                     $scope.subscribeForm.$invalid = true;
                 } else {
                     $scope.messages.fr.exist.show = 0;
                 }

             }).error(function(){ });
        };

        $scope.subscribSubmit = function(isValid){
            if(!isValid) return false;
            $scope.progressbar = true;
            $http({
                url : linksubscribe.ajax_url,
                method : "POST",
                params : {
                    action : "action_atom_mail_save",
                    name : $scope.subscriber.clientName,
                    email : $scope.subscriber.clientEmail,
                    city : JSON.stringify($scope.subscriber.selectedCity),
                    subscrib_key : $scope.subscrib_key
                }

             }).success(function(resp){
                $scope.progressbar = false;
                $scope.messages.fr.success.show = 1;
                $scope.subscriber = {
                    clientName : null,
                    clientEmail :  null,
                    selectedCity : []
                };

                $scope.subscribeForm.$setUntouched();
                $scope.subscribeForm.$setPristine();


             }).error(function(){ });

        };

        $scope.searchTerm;
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
        };
        // The md-select directive eats keydown events for some quick select
        // logic. Since we have a search input here, we don't need that logic.
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
    });

})(window.angular);
