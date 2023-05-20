<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="author" content="Wesley LeMahieu (wlemahieu@mediatemple.net)"/>
	<title>Matrix</title>
	<link rel="stylesheet" type="text/css" href="semantic-ui/semantic.min.css">

	<link rel="stylesheet" type="text/css" href="semantic-ui/components/accordion.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/ad.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/breadcrumb.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/button.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/card.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/checkbox.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/comment.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/container.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/dimmer.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/divider.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/dropdown.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/embed.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/feed.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/flag.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/form.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/grid.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/header.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/icon.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/image.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/input.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/item.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/label.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/list.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/loader.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/menu.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/message.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/modal.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/nag.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/popup.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/progress.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/rail.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/rating.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/reset.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/reveal.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/search.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/segment.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/shape.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/sidebar.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/site.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/statistic.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/step.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/sticky.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/tab.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/table.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/transition.min.css">
	<link rel="stylesheet" type="text/css" href="semantic-ui/components/video.min.css">

	<link rel="stylesheet" type="text/css" href="css/semantic.override.css">
	<link rel="stylesheet" type="text/css" href="css/angular-material.min.css">
	
	<link rel="icon" type="image/png" href="/img/matrix.png">
</head>
<body>

	<!-- Universal Sidebar Menu -->
	<div ng-controller="sidebarCtrl" class="ui vertical inverted sidebar menu">

	    <a href="/#/" class="item header">Matrix</a>
	    
	    <!-- Company Resources -->
        <a class="item" href="http://academy.mediatemple.net/" target="_blank">Academy</a>
        <a class="item" href="https://astronomer.mtvoip.net/" target="_blank">Astronomer</a>
        <a class="item" href="https://tools.mtsvc.net/confluence/" target="_blank">Confluence</a>
        <a class="item" href="https://hostops.mediatemple.net/" target="_blank">HostOps</a>
        <a class="item" href="http://hq.mediatemple.net/" target="_blank">HQ Blog</a>
        <a class="item" href="https://tools.mtsvc.net/jira/" target="_blank">JIRA</a>

	    <!-- Login -->
	    <a ng-cloak ng-show="userinfo === undefined && !loading" class="item" ng-click="login()">
	        Enter
	    </a>

	    <!-- Logout (non-agents exit) -->
	    <a ng-show="userinfo.type != 'Agent' && userinfo !== undefined" class="item" ng-click="logout()">
	        <span ng-cloak ng-show="!loading">Exit</span>
	        <span ng-cloak ng-show="loading">Wait to exit...</span>
	    </a>
	</div>

    <!-- All Content -->
    <div class="pusher">

		<!-- user-dept navbar  -->
		<navbar></navbar>
	
		<!-- content routes -->
		<div class="ui inverted main" id="content" ng-view></div>
	</div>

	<!-- we should handle this via grunt / gulp, but for now... -->
	<script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="semantic-ui/semantic.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/accordion.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/api.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/checkbox.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/colorize.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/dimmer.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/dropdown.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/progress.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/embed.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/form.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/modal.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/nag.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/popup.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/progress.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/rating.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/search.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/shape.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/sidebar.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/site.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/state.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/sticky.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/tab.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/transition.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/video.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/visibility.min.js"></script>
	<script type="text/javascript" src="semantic-ui/components/visit.min.js"></script>

	<script type="text/javascript" src="/js/angular/lib/angular-1.4.3.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-route.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-route-helper.1.0.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-animate.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-aria.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-messages.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-material.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-cookies-1.4.3.min.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angularjs-pay-periods.1.0.js"></script>
	<script type="text/javascript" src="/js/angular/lib/angular-readable-time.1.3.js"></script>
	<script type="text/javascript" src="/js/angular/lib/ng-order-object-by.js"></script>

	<script type="text/javascript" src="/js/angular/core/bootstrap.js"></script>
	<script type="text/javascript" src="/js/angular/core/global.js"></script>
	<script type="text/javascript" src="/js/angular/core/matrix.js"></script>
	<script type="text/javascript" src="/js/angular/core/agents.js"></script>
	<script type="text/javascript" src="/js/angular/core/leadership.js"></script>
	<script type="text/javascript" src="/js/angular/core/cs_leadership.js"></script>
	<script type="text/javascript" src="/js/angular/core/cs_agent.js"></script>
	<script type="text/javascript" src="/js/angular/core/cs_lead.js"></script>
	<script type="text/javascript" src="/js/angular/core/cs_manager.js"></script>
	<script type="text/javascript" src="/js/angular/core/ct_leadership.js"></script>
	<script type="text/javascript" src="/js/angular/core/ct_agent.js"></script>
	<script type="text/javascript" src="/js/angular/core/ct_lead.js"></script>
	<script type="text/javascript" src="/js/angular/core/ct_manager.js"></script>
	<script type="text/javascript" src="/js/angular/core/cs_community.js"></script>
	<script type="text/javascript" src="/js/angular/core/sales_leadership.js"></script>
	<script type="text/javascript" src="/js/angular/core/sales_agent.js"></script>
	<script type="text/javascript" src="/js/angular/core/sales_lead.js"></script>
	<script type="text/javascript" src="/js/angular/core/sales_manager.js"></script>
	<script type="text/javascript" src="/js/angular/core/onboardee.js"></script>

	<script type="text/javascript" src="/js/angular/shared/leadership/attendance.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/checkins.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/contribution_targets.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/enps.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/nps.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/oneonones.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/profiles.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/schedules.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/selectors/agents.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/selectors/teams.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/statistics.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/teams.js"></script>

	<script type="text/javascript" src="/js/angular/shared/agents/1on1s.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agents/attendance.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agents/caller_info.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agents/exceptions_controller.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agents/status.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agents/tailor_progress_bars.js"></script>

	<script type="text/javascript" src="/js/angular/shared/after_call_survey.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agent_emulator.js"></script>
	<script type="text/javascript" src="/js/angular/shared/agent_exit.js"></script>
	<script type="text/javascript" src="/js/angular/shared/alerts.js"></script>
	<script type="text/javascript" src="/js/angular/shared/api.js"></script>
	<script type="text/javascript" src="/js/angular/shared/channel_controller.js"></script>
	<script type="text/javascript" src="/js/angular/shared/checkins.js"></script>
	<script type="text/javascript" src="/js/angular/shared/panel_cookies.js"></script>
	<script type="text/javascript" src="/js/angular/shared/panel_filters.js"></script>
	<script type="text/javascript" src="/js/angular/shared/team_filter.js"></script>
	
	<script type="text/javascript" src="/js/angular/shared/leadership/parameters.js"></script>
	<script type="text/javascript" src="/js/angular/shared/leadership/workflow.js"></script>

	<!-- USERVOICE WIDGET -->
	<script type="text/javascript" src="/js/uservoice.js"></script>
</body>
</html>
