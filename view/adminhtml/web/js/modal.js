require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function(
        $,
        modal
    ) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass: 'magesms-modal',
            buttons: [{
                text: $.mage.__('Close'),
                class: 'action-primary',
                click: function () {
                    this.closeModal();
                }
            }],
            opened: function () {
                function invokeMiddleware(data, config)
                {
                    for(let index = 0; index < _middlewares.length; index++){
                        _middlewares[index](data, config);
                    }
                }

                var params = $("#magesms-order-sendsms").data('params');

                topefekt.widgets.authenticator.url = params.authUrl;
                topefekt.widgets.widget.load(
                    document.getElementById("react-app-root"),
                    "ModuleComponents:sendSms",
                    {
                        application: {
                            id: params.appId,
                            product: "ms",
                            salt: params.salt,
                            language: params.lang,
                            url: params.authUrl
                        },
                        params: {
                            id: params.id,
                            key: params.key
                        },
                        options: {
                            proxy: function(reducerName, requestData){
                                var proxyData = {};
                                var {url, params} = proxyData[requestData.url] || {};

                                if (url){
                                    requestData.contentType = "application/x-www-form-urlencoded";
                                    requestData.url = url;
                                    requestData.data = {__bulkgate: requestData.data, ...params};
                                    return true;
                                }
                            }
                        },
                        events: {

                            onLoadData: function (data) {
                                let {init} = data;
                                /*invokeMiddleware({
                                    init:{
                                        ...init,
                                        env: {homepage: {}} // env.homepage this is due to modules embedded javascript code need this variable. todo: remove when refactoring modules to new widget-api
                                    }
                                });*/
                            },

                            onLoadAsset: function (tag, widget) { //external configuration
                                if (tag === "configuration" && __react_app_data[tag]) {
                                    widget.setOptions(__react_app_data[tag]);
                                } else if (tag === "environment" && __react_app_environment) { //environment
                                    widget.initialize({env: __react_app_environment});
                                }
                            }
                        }
                    },
                    {}
                );
            }
        };
        var popup = $('<div class="ui-dialog-content ui-widget-content"><div id="mage-sms" style="--primary: #e85d22;--secondary: #0073aa;"><div id="react-app-root"></div></div></div>').modal(options);
        $("#magesms-order-sendsms").on('click', function () {
            popup.modal('openModal');
        });
    }
);
