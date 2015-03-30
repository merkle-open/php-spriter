@media only screen and (-webkit-min-device-pixel-ratio: {{ratio}}),
only screen and (-o-min-device-pixel-ratio: {{ratioFrag}}),
only screen and (min-device-pixel-ratio: {{ratio}}) {
	.icon,
	.icon-after:after,
	.icon-before:before {
		background-image: url({{sprite}});
		-webkit-background-size: {{width}} {{height}};
		-moz-background-size: {{width}} {{height}};
		background-size: {{width}} {{height}};
	}
}
