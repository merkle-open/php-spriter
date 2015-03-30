.icon, .icon-after:after, .icon-before:before {
	background-image: url({{sprite}});
	background-repeat: no-repeat;
	background-size: {{width}} {{height}};
	display: inline-block;
}

.icon-after:after, .icon-before:before {
	top: 0;
	margin: 0;
	padding: 0;
	content: "";
	display: inline-block;
	position: relative;
}

.icon-after:after {
	right: 0;
}

.icon-before:before {
	left: 0;
}
