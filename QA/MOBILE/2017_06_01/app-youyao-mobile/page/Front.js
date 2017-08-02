var SiteTracker = function(s, p, r, u) {
	if (s != undefined && s != null) {
		this.site = s
	}
	if (p != undefined && p != null) {
		this.page = p
	}
	if (r != undefined && r != null) {
		this.referer = r
	}
	if (u != undefined && u != null) {
		this.uid = u
	}
};
SiteTracker.prototype.getCookie = function(sKey) {
	if (!sKey || !this.hasItem(sKey)) {
		return null
	}
	return decodeURIComponent(document.cookie.replace(new RegExp(
			"(?:^|.*;\\s*)"
					+ encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&")
					+ "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"), "$1"))
};
SiteTracker.prototype.hasItem = function(sKey) {
	return new RegExp("(?:^|;\\s*)"
			+ encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&")
			+ "\\s*\\=").test(document.cookie)
};
SiteTracker.prototype.track = function(t_params) {
	this.buildParams();
	var src = "";
	if (typeof t_params == "undefined"
			|| typeof t_params.target_url == "undefined") {
		src = "http://taiwan.ch99.dev.youyao.com/action/log/?"
	} else {
		src = t_params.target_url
	}
	if (typeof this.params["site"] == "undefined") {
	}
	for ( var k in this.params) {
		src += k + "=" + encodeURIComponent(this.params[k]) + "&"
	}
	src = src.substr(0, src.length - 1);
	var script = document.createElement("script");
	script.src = src;
	script.async = true;
	(document.getElementsByTagName("head")[0] || document
			.getElementsByTagName("body")[0]).appendChild(script)
};
SiteTracker.prototype.buildParams = function() {
	var href = document.location.href;
	var guid = this.getCookie(this.nGuid || "zzk_guid");
	var dtid = this.getCookie(this.nDtid || "dest_id");
	var uid = this.getCookie(this.nUid || "zzk_uid");
	if (this.uid != undefined && this.uid != null) {
		uid = this.uid
	}
	if (uid == undefined || uid == null | uid == "") {
		uid = 0
	}
	var method = "";
	if (this.method != undefined && this.method != null) {
		method = this.method
	}
	this.params = new Object;
	this.params.p = this.page;
	this.params.h = href;
	this.params.r = this.referer;
	this.params.site = this.site;
	this.params.guid = guid;
	this.params.uid = uid;
	this.params.t = (new Date).getTime();
	this.params.dtid = dtid;
	this.params.m = method;
	if (this.screen != undefined) {
		this.params.sc = JSON.stringify(this.screen)
	}
	if (this.cst != undefined && /[0-9]{13}/.test(this.cst)) {
		this.params.lt = this.params.t - parseInt(this.cst)
	}
	if (this.pageName != undefined) {
		this.params.pn = this.pageName
	}
	if (this.customParam != undefined) {
		this.params.cp = this.customParam
	}
};
SiteTracker.prototype.setSite = function(s) {
	this.site = s
};
SiteTracker.prototype.setPage = function(p) {
	this.page = p
};
SiteTracker.prototype.setPageName = function(n) {
	this.pageName = n
};
SiteTracker.prototype.setCookieNames = function(c) {
	this.cookNames = c
};
SiteTracker.prototype.setReferer = function(r) {
	this.referer = r
};
SiteTracker.prototype.setUid = function(u) {
	this.uid = u
};
SiteTracker.prototype.setMethod = function(m) {
	this.method = m
};
SiteTracker.prototype.setNGuid = function(n) {
	this.nGuid = n
};
SiteTracker.prototype.setNDtid = function(n) {
	this.nDtid = n
};
SiteTracker.prototype.setNUid = function(n) {
	this.nUid = n
};
SiteTracker.prototype.setCst = function(n) {
	this.cst = n
};
SiteTracker.prototype.setScreen = function(v) {
	this.screen = v
};
SiteTracker.prototype.setCustomParam = function(v) {
	this.customParam = v
};