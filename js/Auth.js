function Auth() {

	this.onLogin = null;
	this.onLogout = null;

	this.loggedIn = false;
	this.user = null;

}

Auth.prototype = {
	login: function (username, password) {
		
		var self = this;
		
		$.post('index.php?action=login', {
			'username': username,
			'password': password
		}, function (data) {

			if (data.result == 'success') {

				self.loggedIn = true;
				self.user = data.user;

				if (typeof self.onLogin === 'function') {
					self.onLogin();
				}
			} else {
				if (typeof self.onLogout === 'function') {
					self.onLogout();
				}
			}

		}, 'json');

	},
	logout: function () {
		
		var self = this;
		
		$.post('index.php?action=logout', {}, function (data) {
			if (typeof self.onLogout === 'function') {
				self.onLogout();
			}
		}, 'json');
	},
	checkLogin: function () {
		
		var self = this;
		
		$.post('index.php?action=checkLogin', {}, function (data) {
			
			if (data.loggedIn == true) {
				self.loggedIn = true;
				self.user = data.user;
				if (typeof self.onLogin === 'function') {
					self.onLogin();
				}

			} else {
				self.loggedIn = false;
				self.user = null;
				if (typeof self.onLogout === 'function') {
					self.onLogout();
				}
			}

		}, 'json');

	}
}