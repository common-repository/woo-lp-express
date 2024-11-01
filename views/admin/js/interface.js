//JS parameters for every tab (key == ID of tab)

var wclpElements = {
    "accordion": function(){
        jQuery('#accordion-menu .open').click(function() {
            jQuery('#accordion-menu .open').removeClass('open-selected');
            jQuery('#accordion-menu .content').slideUp('normal');
            if(jQuery(this).next().is(':hidden') == true) {
                jQuery(this).addClass('open-selected');
                jQuery(this).next().slideDown('fast');
            }
        });
        jQuery('#accordion-menu .content').hide();
    }
  };

  var wclpModule = {
      "sender-info": function(){
         //uses accordion
         wclpElements.accordion();
      }
  };

  var defaultSelect;

  //Load tab content
  function loadWpAjax(ID,pluginName){
      jQuery.ajax({
          type: 'POST',
          url: '/wp-admin/admin-ajax.php',
          context: this,
          data: {'action': 'lp_load_tab', 'id': ID, 'pluginName': 'woo-lp-express'}
      }).done(function(response) {
              jQuery(".lpe-content").html(response);
              if(typeof wclpModule[ID] == 'function'){
                  wclpModule[ID]();
              }

              if (ID==="shipping-rules") {
                  rulesInit(function() {
                      jQuery(".loading-bar").hide();
                  });
              } else {
                  jQuery(".loading-bar").hide();
              }

              return false;
      });
  }

  function call_courier(order_id, identcode) {
      //Label wrapper container
      var labelWrapper   = jQuery(".label_wrapper");

      if(identcode !== null && identcode !== '') {
          jQuery.ajax({
              type: 'POST',
              url: '/wp-admin/admin-ajax.php',
              data: {'action': 'lp_call_courier', 'order_id': order_id, 'identcode': identcode}})
          .done(function (response) {
              jQuery(".loading-bar").hide();
              labelWrapper.html(response);
              labelWrapper.show();
          });
      } else {
          alert(trans.validateManifestMsg);
          jQuery(".loading-bar").hide();
          labelWrapper.show();
      }

  }

  //Generate shipping label ajax
  function generateLabelAjax(order_id) {
      //Label wrapper container
      var labelWrapper   = jQuery(".label_wrapper");

      //Parcel info field
      var inputValSize   = jQuery("#parcel-info-field");

      //If parcel info field has class weight so it is not terminal
      var inputValWeight = inputValSize.attr('class') === 'weight' ? inputValSize.val() : null;

      //If input value is size (so it is terminal shipping method)
      if(inputValSize.attr('class') !== 'weight' && inputValSize.val().match("[SMLsml]") && inputValSize.val().length === 1) {
          jQuery.ajax({
              type:'POST',
              url: '/wp-admin/admin-ajax.php',
              data: {'action':'lp_generate_label', 'order_id':order_id, 'parcel_info':inputValSize.val()}})
          .done(function(response) {
              jQuery(".loading-bar").hide();
              labelWrapper.html(response);
              labelWrapper.show();
          });
      } else {
          //Invalid
          if(inputValSize.attr('class') !== 'weight') {
              alert(trans.validateSizeMsg);
              jQuery(".loading-bar").hide();
              labelWrapper.show();
          }
      }

      if (inputValWeight != null) {
          if (inputValWeight.match('[1-9]+') && inputValWeight > 0 && inputValWeight < 500) {
              jQuery.ajax({
                  type: 'POST',
                  url: '/wp-admin/admin-ajax.php',
                  data: {'action': 'lp_generate_label', 'order_id': order_id, 'parcel_info': inputValWeight},
                  success: function (response) {
                      jQuery(".loading-bar").hide();
                      labelWrapper.html(response);
                      labelWrapper.show();
                  }
              });
          } else {
              alert(trans.validateWeightMsg);
              jQuery(".loading-bar").hide();
              labelWrapper.show();
          }
      }

  }

  function scrollToTop() {
      jQuery('body, html').animate({scrollTop:0}, 400);
  }

  jQuery(function() {
      //Save settings
      jQuery(".lpe-content").on("click","#wc-lp-form #submit", function(e){
          jQuery(".loading-bar").show();
          e.preventDefault();
          var data = jQuery("#wc-lp-form").serialize();

          //Validate data first then save data
          if(!selectValidation()) {
              jQuery.ajax({
                  type:'POST',
                  url: '/wp-admin/admin-ajax.php',
                  data: {'action':'validate_serialized_data', 'data':data}})
              .done(function(response) {
                  jQuery('.notice-error').remove();
                  jQuery('.notice-success').remove();

                  if(jQuery.parseJSON(response).length === 0) {

                      //Clear trash fixed international data
                      if(jQuery("input[name=option_page]").val() == "wc_sender_settings") {
                          jQuery.ajax({
                              type: 'POST',
                              url: '/wp-admin/admin-ajax.php',
                              data: {'action': 'clear_int_data'}
                          }).done(function() {
                              jQuery.ajax({
                                  type: 'POST',
                                  url: 'options.php',
                                  data: data
                              }).always(function() {
                                  jQuery('.lpe-content').prepend('<div class="notice notice-success">' +
                                      '<p><strong>Duomenys sėkminai išsaugoti!</strong></p>');
                                  jQuery(".loading-bar").hide();
                                  scrollToTop();
                              });
                          });
                      } else {
                          //Just save the data
                          jQuery.post('options.php', data);
                          jQuery('.lpe-content').prepend('<div class="notice notice-success">' +
                              '<p><strong>Duomenys sėkminai išsaugoti!</strong></p>');
                          jQuery(".loading-bar").hide();
                          scrollToTop();
                      }

                      //Reset border colors if data is valid
                      jQuery('.form-table input').each(function() {
                          jQuery(this).css({'border-color':'#ddd'});
                      });
                  } else {
                      jQuery('.lpe-content').prepend('<div class="notice notice-error">' +
                          '<p><strong>Įvesti neteisingi duomenys. Prašome patikrinti ir mėginti dar kartą.</strong></p>');

                      jQuery.parseJSON(response).forEach(function(value, index) {
                          jQuery('#' + value).css({'border-color':'red'});
                      });
                      jQuery(".loading-bar").hide();
                      scrollToTop();

                  }
              });
          } else {
              jQuery('.lpe-content').prepend('<div class="notice notice-error">' +
                  '<p><strong>Pristatymo užsienyje šalių sąraše yra pasikartojančių šalių arba neigiamų reikšmių</strong></p>');
              jQuery(".loading-bar").hide();
              scrollToTop();
          }

      });

      function selectValidation() {
          var validation = true;
          var select = jQuery('form .wc_int_country');
          select.siblings('span').children('.selection').children('.select2-selection').css('border-color',"#ddd");
          for (var i=0; i<select.length; i++) {
              for (var j=0; j<select.length; j++) {
                  if (select[i].value===select[j].value && i!=j) {
                      select.eq(j).siblings('span').children('.selection').children('.select2-selection').css('border-color',"red");
                      select.eq(i).siblings('span').children('.selection').children('.select2-selection').css('border-color',"red");
                      validation = false;
                  }
              }
          }
          jQuery('.wp_lp_fixed_international_container > input').each(function() {
              jQuery(this).css('border-color',"ddd");
              if (jQuery(this).val()<0) {
                  jQuery(this).css('border-color',"red");
                  validation=false;
              }
          });

          return !validation;
      }

      jQuery(".lpe-content").on('change', 'form .wc_int_country', function() {
          jQuery(this).attr('name', 'wc_lp_express_sender_settings['+jQuery(this)[0].value +'_wp_lp_fixed_international]');
          jQuery(this).siblings('input').attr('name', 'wc_lp_express_sender_settings['+jQuery(this)[0].value +'_wp_lp_fixed_international]');
          selectValidation();
      });

      jQuery(".lpe-content").on('click', '#wp_lp_fixed_country_append', function() {
          jQuery('<div class="wp_lp_fixed_international_container"></div>').appendTo(jQuery(this).parent().parent());
          jQuery('body > .wc_int_country').clone().appendTo(jQuery(this).parent().siblings().last());
          jQuery('.wp_lp_fixed_international_container').last().children('select').select2();
          jQuery('.wp_lp_fixed_international_container').last().prev().children('input').clone().appendTo(jQuery(this).parent().siblings().last());
          jQuery('.wp_lp_fixed_international_container').last().find('option[value="all"]').remove();
          var value = jQuery('.wp_lp_fixed_international_container').last().prev().children().find('option:selected')[0].value;
          jQuery('.wp_lp_fixed_international_container').last().children().find('option:selected').removeAttr('selected');
          jQuery('.wp_lp_fixed_international_container').last().children().find('option[value="'+ value +'"]').next('option').attr('selected', 'selected');
          jQuery('.wp_lp_fixed_international_container').last().children('input').attr('value', "");
          jQuery('.wp_lp_fixed_international_container').last().children('input, select').attr('name', 'wc_lp_express_sender_settings['+ jQuery('.wp_lp_fixed_international_container').last().find('select')[0].value +'_wp_lp_fixed_international]');
          jQuery('<button type="button" class="wp_lp_fixed_country_delete">-</button>').appendTo(jQuery(this).parent().siblings().last());
          selectValidation();
      });

      jQuery(".lpe-content").on('click', '.wp_lp_fixed_country_delete', function() {
          jQuery(this).parent().remove();
          selectValidation();
      });


      //Test authentification data
      jQuery(".lpe-content").on("click","#wc-test-auth #submit", function(e) {
          jQuery(".loading-bar").show();
           e.preventDefault();
           //Send request hello world to test data
           var data = jQuery("#wc-lp-form").serialize();

           //Validate data first then save data
           jQuery.ajax({
               type: 'POST',
               url: '/wp-admin/admin-ajax.php',
               data: {'action': 'test_auth_data', 'data': data}})
           .done(function (response) {

               jQuery('.notice-error').remove();
               jQuery('.notice-success').remove();

               if(response.indexOf('Hello') !== -1) {
                   jQuery('.lpe-content').prepend('<div class="notice notice-success"><p><strong>Sėkmingai pavyko prisijungti prie lp-express API.</strong></p></div>');
               } else {
                   jQuery('.lpe-content').prepend('<div class="notice notice-error"><p><strong>' + response + '</strong></p></div>');
               }

               jQuery(".loading-bar").hide();
           });

           return false;
      });

      //Navigation tab clicked
      jQuery('.nav-tab').click(function(){
          //Clear content
          jQuery(".lpe-content").html("");

          //Show loading bar
          jQuery(".loading-bar").show();

          //Remove active class from previos
          jQuery('.nav-tab').removeClass('nav-tab-active');

          //Add active class to current tab
          jQuery(this).addClass('nav-tab-active');

          //Load content
          loadWpAjax(jQuery(this).data("id"),jQuery(this).data("url"));
      });

      //Set default tab 0
      var defaultTab = jQuery(".lp-navigation .nav-tab").eq(0);
      jQuery(defaultTab).addClass("nav-tab-active");

      //Load default tab content
      if(typeof defaultTab != undefined ){
          loadWpAjax(jQuery(defaultTab).data("id"),'woo-lp-express');
      }

  });

  jQuery(document).ready(function() {
      jQuery(document).ajaxComplete(function() {
          if(!jQuery('body>.wc_int_country').length) {
              jQuery('.wc_int_country').eq(0).clone().appendTo('body');
          }
          jQuery('form .wc_int_country').select2();
      });

  });


 // Siuntimo taisykles

 var lpData = [];
 var gotData = false;

 var allRules = ["Kategorija", "Kiekis", "Kaina"];
 var rules = allRules;
 var categories = [];
 var shipping_methods = undefined;


function renderData() {
    for (let item of lpData) {
        jQuery('<div class="lp-shipping-rule-block"></div>').appendTo(jQuery('.lp-shipping-rules-container'));

        jQuery(`<form>
                    <div class="lp-rule-select-block">
                        <label>Nurodykite, kam taikyti taisyklę</label>
                    </div>
                </form>
                <div class="lp-delete-rule-div">
                    <span>Panaikinti taisyklę</span>
                    <button class="lp-delete-rule">X</button>
                </div>
                <button class="lp-add-rule-select">Pridėti kam taikyti</button>`)
            .appendTo(jQuery('.lp-shipping-rules-container').children().last());

        for (let filt in item.filter) {
            jQuery(`<div class="lp-rule-select-div">
                        <button class="delete-rule-select">x</button>
                        <select class="lp-rule-select">
                            <option value="0">Pasirinkite kam taikyti taisyklę</option>
                        </select>
                    </div>`).appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last());

            for (var i=0; i<rules.length; i++ ) {
                if(rules[i]==filt) {
                    jQuery('<option value="'+rules[i]+'" selected>'+rules[i]+'</option>').appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last().children('.lp-rule-select').last());
                } else {
                    jQuery('<option value="'+rules[i]+'">'+rules[i]+'</option>').appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last().children('.lp-rule-select').last());
                }
            }

            if (filt=="Kategorija") {
                jQuery('<select name="categories[]" multiple="multiple" class="category-select lp-filter"></select>').appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last());
                jQuery('.category-select').val(["1"]).trigger("change");

                for(let cat of categories) {
                    jQuery('<option value="'+cat[0]+'" data-id="'+cat[1]+'">'+cat[0]+'</option>').appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last().children('.category-select'));
                }

                jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last().children('.category-select').select2({
                    placeholder: "Pasirtinkite kategorijas",
                    width: "200px"
                });

            } else if (filt=="Kiekis" || filt=="Kaina") {
                jQuery('<input class="lp-filter" type="number" value="'+item.filter[filt]+'" placeholder="nuo kiek taikyti">').appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form').children('.lp-rule-select-block').last().children('.lp-rule-select-div').last());
            }

        }

        jQuery(`<div class="lp-rule-action-select">
                    <label>Nurodykite, taisyklės veiksmą</label>
                    <select id="discount-type-select">
                        <option value="0">Ką taikyti?</option>
                        <option value="discount">Nuolaida pristatymui</option>
                        <option value="free_shipping">Nemokamas pristatymas</option>
                        <option value="disable">Išjungti pristatymą</option>
                        <option value="fixed_price">Fiksuota kaina</option>
                    </select>
                </div>`).appendTo(jQuery('.lp-shipping-rules-container').children().last().children('form'));

        jQuery('.lp-shipping-rules-container').children().last().children('form').find('#discount-type-select').val(item.action.type).trigger('change');

        if (item.action.type=="discount") {
            jQuery('.lp-shipping-rules-container').children().last().children('form').find('.discount-type').val(item.action.amount[0]).trigger('change');
            jQuery('.lp-shipping-rules-container').children().last().children('form').find('.discount-amount').val(item.action.amount[1]).trigger('change');
        }

        if (item.action.type=="fixed_price") {
            jQuery('.lp-shipping-rules-container').children().last().children('form').find('.discount-amount').val(item.action.amount[0]).trigger('change');
        }

        let tempFor = item.action.for.map(item => Object.values(item)[0]);
        jQuery('.lp-shipping-rules-container').children().last().children('form').find('.shipping-methods').val(tempFor).trigger('change');

    }

    for( let i=0; i< jQuery('.category-select').length; i++) {
        jQuery('.category-select').eq(i).val(lpData[i].filter.Kategorija.map(item => item.name)).trigger("change");
    }


}

jQuery(document).on('click', '.lp-add-new-rule-button', function() {
    jQuery('<div class="lp-shipping-rule-block"></div>').appendTo(jQuery('.lp-shipping-rules-container'));
    jQuery(`<form>
                <div class="lp-rule-select-block">
                    <label>Nurodykite, kam taikyti taisyklę</label>
                    <div class="lp-rule-select-div">
                        <button class="delete-rule-select">x</button>
                        <select class="lp-rule-select">
                            <option value="0">Pasirinkite kam taikyti taisyklę</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="lp-delete-rule-div">
                <span>Panaikinti taisyklę</span>
                <button class="lp-delete-rule">X</button>
            </div>
            <button class="lp-add-rule-select">Pridėti kam taikyti</button>`)
        .appendTo(jQuery('.lp-shipping-rules-container').children().last());

    for (var i=0; i<allRules.length; i++ ) {
        jQuery('<option value="'+allRules[i]+'">'+allRules[i]+'</option>').appendTo(jQuery('.lp-shipping-rule-block').last().find('.lp-rule-select'));
    }
});

jQuery(document).on('click', '.lp-add-rule-select', function() {
    jQuery(`<div class="lp-rule-select-div">
                <button class="delete-rule-select">x</button>
                <select class="lp-rule-select">
                    <option value="0">Pasirinkite kam taikyti taisyklę</option>
                </select>
            </div>`).appendTo(jQuery(this).siblings('form').children('.lp-rule-select-block'));

    for (var i=0; i<allRules.length; i++ ) {
        jQuery('<option value="'+allRules[i]+'">'+allRules[i]+'</option>').appendTo(jQuery(this).siblings('form').children('.lp-rule-select-block').children('.lp-rule-select-div').last().children('select'));
    }

    if (jQuery('.lp-shipping-rule-block form').length===3) {
        jQuery(this).remove();
    }
});
jQuery(document).on('click', '.delete-rule-select', function() {
    if(jQuery(this).parent().siblings('.lp-rule-select-div').length) {
        jQuery(this).parent().remove();
    } else {
        jQuery(this).parent().parent().parent().parent().remove();
    }

});

jQuery(document).on('change', '.lp-rule-select', function() {

    jQuery(this).siblings('.category-select, .select2, input').remove();

    if(jQuery(this).parent().siblings('.lp-rule-select-div').length<1) {
        jQuery(this).parent().parent().siblings().remove();

        if(jQuery(this).val() != false) {
            jQuery(`<div class="lp-rule-action-select">
                        <label>Nurodykite, taisyklės veiksmą</label>
                        <select id="discount-type-select">
                            <option value="0">Ką taikyti?</option>
                            <option value="discount">Nuolaida pristatymui</option>
                            <option value="free_shipping">Nemokamas pristatymas</option>
                            <option value="disable">Išjungti pristatymą</option>
                            <option value="fixed_price">Fiksuota kaina</option>
                        </select>
                    </div>`).appendTo(jQuery(this).parent().parent().parent());
        }
    }

    if (jQuery(this).val()=="Kategorija") {
        jQuery('<select name="categories[]" multiple="multiple" class="category-select lp-filter"></select>').appendTo(jQuery(this).parent());
        jQuery(this).parent().find('.category-select').select2({
            placeholder: "Pasirtinkite kategorijas",
            width: "200px"
        });
        for(let cat of categories) {
            jQuery('<option value="'+cat[0]+'" data-id="'+cat[1]+'">'+cat[0]+'</option>').appendTo(jQuery(this).siblings('.category-select'));
        }

    } else if (jQuery(this).val()=="Kiekis" || jQuery(this).val()=="Kaina") {
        jQuery('<input class="lp-filter" type="number" placeholder="nuo kiek taikyti">').appendTo(jQuery(this).parent());
    }

});

jQuery(document).on('change', '#discount-type-select', function() {
    jQuery(this).parent().siblings('.lp-rule-action-for').remove();
    if(jQuery(this).val()!=false) {
        jQuery(`<div class="lp-rule-action-for"><label>Nurodykite, atliekamo veiksmo nustatymus</label></div>`).appendTo(jQuery(this).parent().parent());

        if(jQuery(this).val()=="discount") {
            jQuery(`<div>
                        <select class="discount-type">
                            <option value="fixed">Fiksuota nuolaida</option>
                            <option value="percentage">Procentinė nuolaida</option>
                        </select>
                        <input class="discount-amount" type="number" placeholder="Nurodykite fiksuotą kainą">
                    </div>`).appendTo(jQuery(this).parent().siblings('.lp-rule-action-for'));
        }

        if(jQuery(this).val()=="fixed_price") {
            jQuery(`<div>
                        <input class="discount-amount" type="number">
                    </div>`).appendTo(jQuery(this).parent().siblings('.lp-rule-action-for'));
        }

        jQuery(`<select name="shipping[]" multiple="multiple" class="shipping-methods"></select>`).appendTo(jQuery(this).parent().siblings('.lp-rule-action-for'));
        for (let shipping in shipping_methods) {
            jQuery('<option value="'+Object.values(shipping_methods[shipping])[0]+'" data-key="'+Object.keys(shipping_methods[shipping])[0]+'">'+Object.values(shipping_methods[shipping])[0]+'</option>').appendTo(jQuery(this).parent().siblings('.lp-rule-action-for').find('.shipping-methods'));
        }
        jQuery(this).parent().siblings('.lp-rule-action-for').children('.shipping-methods').select2({
            placeholder: "Kokiems siuntimo metodams taikyti?"
        });
    }
});

jQuery(document).on('click', '.lp-delete-rule', function() {
    jQuery(this).parent().parent().remove();
});

jQuery(document).on('click', '.lp-rules-save-changes input[type="submit"]', function(e) {
    e.preventDefault();
    sendData();
});

function sendData() {
    jQuery('.notice-error').remove();
    jQuery('.notice-success').remove();
    var validation = true;
    jQuery('.lp-shipping-rules-container').find('select, input').not('.select2-search__field').each(function() {
            jQuery(this).css('border-color', '#ddd');
            jQuery(this).parent().find('.select2-selection').css('border-color', '#ddd');
        if (!jQuery(this).val() || jQuery(this).val()<1 ) {
            if (jQuery(this).hasClass('select2-hidden-accessible')) {
                jQuery(this).parent().find('.select2-selection').css('border-color', 'red');
            }
            jQuery(this).css('border-color', 'red');
            validation = false;
        }
    });

    for( var k=0; k<jQuery('.lp-shipping-rule-block').length; k++) {
        for (var i=0; i<jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').length; i++) {
            for (var j=0; j<jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').length; j++) {
                if (jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').eq(i).val() === jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').eq(j).val() && j!==i) {
                    validation = false;
                    jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').eq(i).css('border-color', 'red');
                    jQuery('.lp-shipping-rule-block').eq(k).find('.lp-rule-select').eq(j).css('border-color', 'red');
                }
            }
        }
    }

    if(validation) {
        jQuery(".loading-bar").show();
        var data=[];
        jQuery('.lp-shipping-rule-block').each(function() {
            var tempData = {filter: {}, action: {}};

            jQuery(this).find('.lp-rule-select-div').each(function() {
                tempCat=[];
                if (jQuery(this).find('.lp-rule-select').val() === "Kategorija") {
                    for (let i=0; i<jQuery(this).find('.lp-filter')[0].selectedOptions.length; i++) {
                        var tempObj = {};
                        tempObj.term_id = jQuery(this).find('.lp-filter')[0].selectedOptions[i].getAttribute('data-id');
                        tempObj.name = jQuery(this).find('.lp-filter')[0].selectedOptions[i].getAttribute('value');
                        tempCat.push(tempObj);
                    }
                    tempData.filter[jQuery(this).find('.lp-rule-select').val()] = tempCat;
                } else {
                    tempData.filter[jQuery(this).find('.lp-rule-select').val()] = parseInt(jQuery(this).find('.lp-filter').val());
                }

            });

            tempData.action.type = jQuery(this).find('#discount-type-select').val();

            tempFor = [];

            for (let i=0; i<jQuery(this).find('.shipping-methods')[0].selectedOptions.length; i++) {
                var tempObj = {};
                tempObj[jQuery(this).find('.shipping-methods')[0].selectedOptions[i].getAttribute('data-key')]= jQuery(this).find('.shipping-methods')[0].selectedOptions[i].getAttribute('value');
                tempFor.push(tempObj);
            }

            tempData.action.for = tempFor;

            if (jQuery(this).find('#discount-type-select').val()=="discount") {
                tempData.action.amount = [jQuery(this).find('.discount-type').val(), jQuery(this).find('.discount-amount').val()];
            }

            if (jQuery(this).find('#discount-type-select').val()=="fixed_price") {
                tempData.action.amount = jQuery(this).find('.discount-amount').val();
            }

            data.push(tempData);
        });
        jQuery.post('/wp-admin/admin-ajax.php', {'action': 'lpx-save-shipping-rules', "data": data})
            .done(function(response) {
                jQuery(".loading-bar").hide();
                jQuery('.lpe-content').prepend('<div class="notice notice-success">' +
                    '<p><strong>Duomenys sėkminai išsaugoti!</strong></p>');
                setTimeout(function(){
                    jQuery('.notice-success').remove();
                }, 5000);
        });

    } else {
        jQuery('.lpe-content').prepend('<div class="notice notice-error">' +
            '<p><strong>Patikrinkite įvestus laukus. Blogai užpildyti laukai pažymėti raudonai</strong></p>');
    }

}

//INIT

function rulesInit(cb) {
    categories=[];
    lpData=[];
    shipping_methods=[];

    jQuery.post('/wp-admin/admin-ajax.php', {'action': 'lpx-product-categories'}).done(function(data) {
        categories=jQuery.parseJSON(data).map(item => [item.name, item.term_id]);
        if (categories.length && shipping_methods && gotData) {
            renderData();
            cb();
        }
    });

    jQuery.post('/wp-admin/admin-ajax.php', {'action': 'lpx-shipping-methods'}).done(function(data) {
        shipping_methods = jQuery.parseJSON(data);
        console.log(shipping_methods);
        if (categories.length && shipping_methods && gotData) {
            renderData();
            cb();
        }
    });

    jQuery.post('/wp-admin/admin-ajax.php', {'action': 'lpx-get-shipping-rules'})
        .done(function(response) {
            lpData = jQuery.parseJSON(response);
            if (!lpData) {
                lpData=[];
            }
            gotData = true;
            if (categories.length && shipping_methods && gotData) {
                renderData();
                cb();
            }
    });
}
