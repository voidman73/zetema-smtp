jQuery(document).ready(function ($) {
  console.log("Zetema SMTP Admin JS loaded");

  var modal = $("#provider-modal");
  $('.add-provider, #add-provider-button').on('click', function() {
    $('#step-provider').show();
    $('#step-config').hide();
    modal.show();
});

$('#import').on('click', function() {
    var import_nonce = $(this).data('import-nonce');
    var plugin = $(this).data('plugin');

    $.ajax({
        url: ProMailSMTPAdminProviders.ajaxUrl,
        method: 'POST',
        data: {
            action: 'pro_mail_smtp_import_connections',
            nonce: import_nonce,
            plugin: plugin
        },
        success: function(response) {
            if (response.success) {
                alert('Import completed successfully!');
                location.reload();
            } else {
                alert('Import failed: ' + (response.data || 'Unknown error'));
                location.reload();
            }
        }
    });
});
  // Close modal
  $(".modal-close").on("click", function (e) {
    e.preventDefault();
    modal.hide();
  });

  var modal = $("#provider-modal");

  // Provider card selection
  $('.provider-card').on('click', function() {
    var provider = $(this).data('provider');
    
    $.ajax({
        url: ProMailSMTPAdminProviders.ajaxUrl,
        method: 'POST',
        data: {
            action: 'pro_mail_smtp_load_provider_form',
            provider: provider,
            nonce: ProMailSMTPAdminProviders.nonce
        },
        success: function(response) {
            if (response.success) {
                $('#step-config').html(response.data.html);
                $('#step-provider').hide();
                $('#step-config').show();
                $('#provider-form .button-primary').text('Add Provider');
            } else {
                alert('Error loading provider form');
            }
        }
    });
});


  // Back button handler
  $(document).on("click", ".back-step", function () {
    $("#step-config").hide();
    $("#step-provider").show();
  });

 // Form submission handler
$(document).on('submit', '#provider-form', function(e) {
    e.preventDefault();    
    $.ajax({
        url: ProMailSMTPAdminProviders.ajaxUrl,
        method: 'POST',
        data: {
            action: 'pro_mail_smtp_save_provider',
            formData: $(this).serialize(),
            nonce: ProMailSMTPAdminProviders.nonce
        },
        success: function(response) {
            
            if (response.success) {
                location.reload();
            } else {
                alert('Error saving provider: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Save Error:', error);
            alert('Network error while saving provider');
        }
    });
});

  $("head").append(`
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 600px;
                position: relative;
            }
            .provider-card {
                cursor: pointer;
                padding: 15px;
                border: 1px solid #ddd;
                margin-bottom: 10px;
            }
            .provider-card:hover {
                background-color: #f0f0f0;
            }
        </style>
    `);
$('.test-provider').on('click', function(e) {
    e.preventDefault();
    var button = $(this);
    button.prop('disabled', true).text('Testing...');
    $.ajax({
        url: ProMailSMTPAdminProviders.ajaxUrl,
        method: 'POST',
        data: {
            action: 'pro_mail_smtp_test_provider_connection',
            nonce: ProMailSMTPAdminProviders.nonce,
            connection_id: button.data('connection_id')
        },
        success: function(response) {
            if (response.success) {
                alert('Connection test completed successfully!');
            } else {
                alert('Connection failed: ' + (response.data || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Test Error:', error);
            alert('Error testing connection.');
        },
        complete: function() {
            button.prop('disabled', false).text('Test');
        }
    });
});

  // Delete Provider
  $(".delete-provider").on("click", function () {
    if (!confirm("Are you sure you want to delete this provider?")) {
      return;
    }

    var button = $(this);
    var connection_id = button.data("connection_id");
    $.ajax({
      url: ProMailSMTPAdminProviders.ajaxUrl,
      method: "POST",
      data: {
        action: "pro_mail_smtp_delete_provider",
        connection_id: connection_id,
        nonce: ProMailSMTPAdminProviders.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert("Error deleting provider");
        }
      },
    });
  });

  $('.edit-provider').on('click', function() {
    var connection_id = $(this).data('connection_id');
    var config = $(this).data('config');
    
    $('#step-config').html('<div class="loading">Loading...</div>').show();
    $('#step-provider').hide();
    modal.show();
    $.ajax({
        url: ProMailSMTPAdminProviders.ajaxUrl,
        method: 'POST',
        data: {
            action: 'pro_mail_smtp_load_provider_form',
            provider: config.provider,
            nonce: ProMailSMTPAdminProviders.nonce,
            connection_id: connection_id
        },
        success: function(response) {
            if (response.success) {
                $('#step-config').html(response.data.html);
                $('#provider-form .button-primary .save-provider').text('Update Provider');
                var data = config;
                data.index = connection_id;

                fillInputs(data);
            } else {
                alert('Error loading provider form');
            }
        }
    });
});

  // Settings Toggle Functionality
  const toggle = $('.toggle-settings');
  const content = $('.settings-content');

  toggle.on('click', function() {
      const isExpanded = toggle.attr('aria-expanded') === 'true';
      toggle.attr('aria-expanded', !isExpanded);
      content.toggleClass('expanded');
  });
  
  // Banner Dismissal
  $('.dismiss-banner').on('click', function() {
      $(this).closest('.import-banner').fadeOut(300, function() {
          $(this).remove();
      });
  });
});
