angular.module('starter.translate', ['pascalprecht.translate'])
.config(function($translateProvider){
	//Config language
	$translateProvider.preferredLanguage("en");
	$translateProvider.fallbackLanguage("en");
	// Enable escaping of HTML
	$translateProvider.useSanitizeValueStrategy('escapeParameters');
	//Translate for EN
	$translateProvider.translations('en', {
		disconnected: "Network disconnected",
		photos: "Photos",
		videos: "Videos",
		latest: "LATEST",
		topnew: "TOP NEW",
		video: "VIDEO",
		trending: "TRENDING",
		thisArticle: "This post has",
		comment: "comment",
		comments: "comments",
		addComment: "ADD A COMMENT",
		nextStory: "NEXT STORY",
		Comment: "Comment",
		noData: "NO DATA",
		logout: "Logout",
		myBookmarks: "My bookmarks",
		appSettings: "App Settings",
		support: "Support",
		rateApp: "Rate us on store",
		shareApp: "Share this app",
		information: "Information",
		aboutUs: "About us",
		termsOfUse: "Terms of use",
		privacyPolicy: "Privacy policy",
		version: "Version",
		versionNumber: "1.0.1",
		news: "News",
		photo: "Photo",
		bookmark: "Bookmarks",
		noBookmark: "No bookmarks found",
		logIn: "Log in",
		username: "Username",
		password: "Password",
		forgotPassword: "Forgot password?",
		notMember: "Not a member yet?",
		signUpNow: "SIGN UP NOW",
		resetPassword: "Reset Password",
		enterEmail: "Enter your register email address",
		email: "Email",
		register: "Register",
		weNeed: "We need a few more details about you before we set up your account",
		firstName: "First Name",
		lastName: "Last Name",
		rePassword: "Re-Password",
		iAgree: "I agree to the",
		termsConditions: "Daily News Terms & Conditions",
		keyword: "Keyword",
		emptyList: "Empty list",
		noResult: "No results avalible",
	});
	//End Translate for EN
});