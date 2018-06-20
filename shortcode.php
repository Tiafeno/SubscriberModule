<?php

class FALI_shortcode_subscribe {
	public function __construct() {

	}

	public static function render( $attrs, $content ) {
		$at = shortcode_atts( array(
			'subscrib_key' => 'subscrib'
		), $attrs );

		wp_enqueue_style( 'angular-material-style', plugins_url( '/assets/components/angular-material/angular-material.min.css', __FILE__ ),
			array() );
		wp_enqueue_style( 'subscribe-style', plugins_url( '/assets/css/subscribe.css', __FILE__ ),
			array() );
		wp_enqueue_script( 'angular', plugins_url( '/assets/components/angular/angular.js', __FILE__ ), array(), false, false );

		wp_enqueue_script( 'angular-aria', plugins_url( '/assets/components/angular-aria/angular-aria.js', __FILE__ ),
			array(), false, false );
		wp_enqueue_script( 'angular-messages', plugins_url( '/assets/components/angular-messages/angular-messages.js', __FILE__ ),
			array(), false, false );
		wp_enqueue_script( 'angular-animate', plugins_url( '/assets/components/angular-animate/angular-animate.js', __FILE__ ),
			array(), false, false );
		wp_enqueue_script( 'angular-material', plugins_url( '/assets/components/angular-material/angular-material.js', __FILE__ ),
			array(), false, false );

		wp_enqueue_script( 'controller', plugins_url( '/assets/js/App.js', __FILE__ ),
			array( 'jquery' ), false, false );

		wp_localize_script( 'controller', 'linksubscribe', array(
			'ajax_url'           => admin_url( 'admin-ajax.php' ),
			'assets_plugins_url' => plugins_url( '/assets/', __FILE__ )
		) );

		?>
		<div ng-app="SubscribeApp" ng-controller="AppCtrl" class="ng-scope">
			<md-content layout-padding>
				<form name="subscribeForm" ng-submit="subscribSubmit(subscribeForm.$valid)" novalidate>
					<div layout="row" layout-sm="column" layout-align="space-around">
						<md-progress-circular md-mode="indeterminate" ng-show="progressbar" md-diameter="40"></md-progress-circular>
					</div>
					<md-subheader ng-show="messages.fr.warn.show" class="md-warn" style="text-align:center">
						{{messages.fr.warn.msg}}
					</md-subheader>
					<md-subheader ng-show="messages.fr.success.show" style="text-align:center">{{messages.fr.success.msg}}
					</md-subheader>
					<md-subheader ng-show="messages.fr.exist.show" class="md-warn" style="text-align:center">
						{{messages.fr.exist.msg}}
					</md-subheader>

					<md-input-container flex="50" class="md-block">
						<label>Nom</label>
						<input ng-cust required name="clientName" ng-model="subscriber.clientName">
						<div ng-messages="subscribeForm.clientName.$error">
							<div ng-message="required">This is required.</div>
						</div>
					</md-input-container>
					<md-input-container ng-show="false">
						<input ng-value="subscrib" ng-model="subscrib_key">
					</md-input-container>


					<md-input-container flex="50" class="md-block">
						<label>Email</label>
						<input required type="email" name="clientEmail" ng-blur="checkMailExist()" ng-model="subscriber.clientEmail"
						       ng-pattern="/^.+@.+\..+$/"/>

						<div ng-messages="subscribeForm.clientEmail.$error" role="alert">
							<div ng-message-exp="['required', 'pattern']">
								Email invalid
							</div>
						</div>
					</md-input-container>

					<md-input-container flex="50" class="md-block">
						<label>Projets</label>
						<md-select ng-model="subscriber.selectedCity"
						           md-on-close="clearSearchTerm()"
						           data-md-container-class="selectdemoSelectHeader"
						           multiple required>
							<md-select-header class="demo-select-header">
								<input ng-model="searchTerm"
								       type="search"
								       placeholder="Rechercher un projet.."
								       class="demo-header-searchbox md-text">
							</md-select-header>
							<md-optgroup label="city">
								<md-option ng-value="thecity.slug" ng-repeat="thecity in city |
                                filter:searchTerm">{{thecity.name}}
								</md-option>
							</md-optgroup>
						</md-select>
					</md-input-container>

					<div style="margin-top:40px;">
						<md-button type="submit" ng-disabled="subscribeForm.$invalid" ng-click=""
						           style="margin:auto; display:block">Send
						</md-button>
					</div>

				</form>
			</md-content>
		</div>

		<?php

		return $content;
	}
}
