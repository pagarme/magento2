require([
    "jquery",
    "jquery/ui",
], function ($) {
    function Hub(config) {
        this.urlToIntegrate = `https://${config.env}hub.mundipagg.com/{language}/apps/${config.publicAppKey}/authorize?redirect=${config.redirectUrl}`;
        this.urlToView = `https://${config.env}hub.mundipagg.com/{language}/apps/${config.publicAppKey}/edit/${config.installId}`;

        this.urlToIntegratePtBr = `https://${config.env}hub.mundipagg.com/apps/${config.publicAppKey}/authorize?redirect=${config.redirectUrl}`;
        this.urlToViewPtBr = `https://${config.env}hub.mundipagg.com/apps/${config.publicAppKey}/edit/${config.installId}`;

        this.mundipaggLogo = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAgCAYAAAAFQMh/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo4ODAzOTNBNUFBQzFFODExOTZGQkYzRTY4RUMyNDhGRCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpBQzA4N0Y2MkMxQUExMUU4OTk1QkU2MDk5QUM5OTYzMiIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpBQzA4N0Y2MUMxQUExMUU4OTk1QkU2MDk5QUM5OTYzMiIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M2IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjg4MDM5M0E1QUFDMUU4MTE5NkZCRjNFNjhFQzI0OEZEIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjg4MDM5M0E1QUFDMUU4MTE5NkZCRjNFNjhFQzI0OEZEIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+pk4v1AAAAa5JREFUeNpi+P//fxMQ3wZiCyBmoDFWAOI7QLwMxPn3HwJAtCUNLVUG4ndQu8ACL/6jAlsaWKoOxB+Q7HjLxMDA8JcBFRwCYi8G6gFDID4DxPxIYv+ZcCjeCsRuVLBUF4hPATEPugQTHk07gdiVAkv1oZayYJNkIqB5FxB7kGGpEdRSDlwKmIgwZDsQ+5FgqRnUUjZ8ipiINGwjEAcSoc4CiE8AMTMhhUwk+GQdEPvjkbcE4uNAzEiMYUwkxt0GII7Eos8aiI+RYhATGQlnGRDrIPHtgfgIqYaQY3EjEN9CSkgHyMpswOLr6X/iwXakYtDhP/ngDak+rkAqBvdTUqSRYvEfIL4LZddQWpaSYvF/JDYXPS1mBWIJKLuHnhaDQB2U3gvECRTZTGKqBoFUIGaEpuwQclM1ORaDgB5StgqhR3YCgVIgvo3EXwPE4bQO6hw87apQWgV1DhGNulBqW5xEQovSk1oWJ5PRnPWh1OIUCtrSXuRanEqFhrw3qRYnU7EX4YnL4mdoghE06MI4odnxlgFNIIyGnTaUhgNIYCY0uMPo0E0FWf4E1JIBCDAASX+haFjXN1cAAAAASUVORK5CYII=";
        this.space = "&nbsp;";
        this.elementType = "button";
        this.containerId = "botao-hub";
        this.locations = {
            "pt-br": {
                integrate: "Integrar com a Mundipagg",
                view: "Visualizar integração",
                language: "pt-BR"
            },
            "en-us": {
                integrate : "Integrate with Mundipagg",
                view: "View integration",
                language: "en-US"
            },
            "es-es": {
                integrate: "Integrar con Mundipagg",
                view: "Ver integración",
                language: "es-ES"
            }
        };
        this.language = "pt-br";

        this.setup = function(config) {

            if (config.language) {
                this.language = config.language.toLowerCase();
            }

            if (config.installId) {
                var url =  (this.language == "pt-br") ? this.urlToViewPtBr : this.urlToView;
                text = this.locations[this.language].view;
            } else {
                var url =  (this.language == "pt-br") ? this.urlToIntegratePtBr : this.urlToIntegrate;
                text = this.locations[this.language].integrate;
            }

            url = url.replace("{language}", this.locations[this.language].language);
            url = url.replace("{publicAppKey}", config.publicAppKey);
            url = url.replace("{installId}", config.installId);

            createButton(text, function(event) {
                event.preventDefault();
                window.location.href = url;
            });
        };

        this.createButton = function(text, func) {
            var container = document.getElementById(this.containerId);
            var button = document.createElement(this.elementType);
            button.innerHTML = text + this.space + this.space + this.getImageTag(mundipaggLogo);
            button.onclick = func;
            container.appendChild(button);
        };

        this.getImageTag = function(src) {
            return "<img src=\"" + src + "\" />";
        };

        this.setup(config);

        return this;
    }

    var magento2 = '1e4679e9-5886-4d74-8e10-bbca1732b1fb'; // STG
    var installId = '';

    // hub config
    var config = {
        env : 'stg', // '' -> PROD
        publicAppKey : magento2,
        installId : installId,
        redirectUrl : `${window.location.href}`,
        language: "pt-BR"
    };

    // run and create button
    Hub(config);

});
