package com.thm.mni.tals;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.JsonRequest;

import org.json.JSONException;

/**
 * Activity Class for the Pin Input Activity.
 * Extends AppCompatActivity like all other Activities in this application.
 * Uses the Moodle Webservices to check the status of the appointment, and to send the pin.
 * Implements a SwipeRefreshlayout.
 */
public class PinInputActivity extends AppCompatActivity implements SwipeRefreshLayout.OnRefreshListener {

    private static final String TAG = PinInputActivity.class.getSimpleName();
    private SwipeRefreshLayout swipeRefreshLayout;
    private Button pinButton;
    private CheckBox pinCheckBox;
    private EditText pinEditText;
    private LinearLayout pinArea;
    private TextView pinInfoTextView;
    private String userid;
    private String token;
    private AppointmentData appointmentData;
    private Context context;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_pin_input);

        token = this.getIntent().getStringExtra(LoginActivity.TOKEN_ID);
        userid = this.getIntent().getStringExtra(LoginActivity.USER_ID);
        if(token == null || userid == null) {
            DatabaseAdapter databaseAdapter = new DatabaseAdapter(this);
            databaseAdapter.open();
            token = databaseAdapter.getToken();
            userid = databaseAdapter.getUserId();
            databaseAdapter.close();
        }
        appointmentData = (AppointmentData) this.getIntent().getSerializableExtra(CourseListAdapter.APPOINTMENT_DATA);
        if(appointmentData == null) {
            onBackPressed();
        }

        getSupportActionBar().setTitle(appointmentData.getCourseTitle());
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setDisplayShowHomeEnabled(true);

        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout);
        swipeRefreshLayout.setOnRefreshListener(this);

        pinButton = findViewById(R.id.pinButton);
        pinCheckBox = findViewById(R.id.pinCheckBox);
        pinEditText = findViewById(R.id.pinEditText);

        pinArea = findViewById(R.id.pinArea);
        pinInfoTextView = findViewById(R.id.pinInfoTextView);

        pinEditText.setOnEditorActionListener((v, actionId, event) -> {
            if(actionId == EditorInfo.IME_ACTION_DONE) {
                pinButton.performClick();
                return true;
            }
            return false;
        });


        pinButton.setOnClickListener(v -> {
            if (pinEditText.getText().length() > 0 && pinCheckBox.isChecked()) {
                sendPin(pinEditText.getText().toString());
                pinEditText.setText(getString(R.string.blank));
            } else {
                if (pinEditText.getText().length() == 0) {
                    pinEditText.setError(getString(R.string.enter_pin_error));
                }
                if (!pinCheckBox.isChecked()) {
                    pinCheckBox.setError(getString(R.string.confirm_missing_days));
                }
            }
        });
        swipeRefreshLayout.post(() -> {
            swipeRefreshLayout.setRefreshing(true);
            fetchPinInfo();
        });
        context = this;
    }

    private void enableInput() {
        pinButton.setClickable(true);
        pinButton.setAlpha(1f);
        pinEditText.setEnabled(true);
        pinCheckBox.setEnabled(true);
    }

    private void disableInput() {
        pinButton.setClickable(false);
        pinButton.setAlpha(.4f);
        pinEditText.setEnabled(false);
        pinCheckBox.setEnabled(false);
    }

    private void sendPin(String pin) {
        if(appointmentData == null) {
            onBackPressed();
        }
        disableInput();
        pinButton.setText(R.string.sending_pin);

        String url = MyUrls.getSendPinRequestUrl(token,userid,appointmentData.getAppointmentid(),pin);
        Log.d(TAG, "URL: " + url);
        final JsonRequest request = new JsonObjectRequest(url, null, response -> {
            //do stuff with the response
            boolean result;
            try {
                result = response.getBoolean("response");
            } catch (JSONException e) {
                e.printStackTrace();
                result = false;
            }
            pinButton.setText(getString(R.string.enter_pin));
            if(!result) { enableInput(); }
            if(MyDebug.DEBUG) Log.d(TAG, response.toString());
            if(result) pinInfoTextView.setText(getString(R.string.already_attending));
            AlertDialog alertDialog = new AlertDialog.Builder(context).create();
            alertDialog.setMessage(result ? getString(R.string.pin_confirmed) : getString(R.string.pin_not_confirmed));
            alertDialog.setCanceledOnTouchOutside(false);
            alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK", (dialog, which) -> dialog.dismiss());
            alertDialog.show();

        }, error -> {
            if(MyDebug.DEBUG) Log.e(TAG, "Server Error: " + error.getMessage());
            pinButton.setText(R.string.enter_pin);
            Toast.makeText(getApplicationContext(), error.getMessage().contains("UnknownHost") ? getString(R.string.network_error) : getString(R.string.generic_error), Toast.LENGTH_LONG).show();
        });

        TalsApp.getInstance().addToRequestQueue(request);
    }

    private void fetchPinInfo() {
        if(appointmentData == null) {
            onBackPressed();
        }
        swipeRefreshLayout.setRefreshing(true);
        pinArea.setVisibility(View.GONE);
        if(MyDebug.DEBUG) Log.d(TAG, "THE APPOINTMENT ID IS" + appointmentData.getAppointmentid());
        String url = MyUrls.getFetchPinInfoUrl(token, userid, appointmentData.getAppointmentid());
        final JsonRequest request = new JsonObjectRequest(url, null, response -> {
            //do stuff with the response
            if(MyDebug.DEBUG) Log.d(TAG, response.toString());
            swipeRefreshLayout.setRefreshing(false);
            pinArea.setVisibility(View.VISIBLE);
            try {
                if (response.has("exception") && response.has("message")) {
                    String errorString;
                    try {
                        errorString = response.getString("message");
                        if(MyDebug.DEBUG) Log.d(TAG, errorString);
                        errorString = errorString != null ? errorString.contains("Ungültiges Token") ? getString(R.string.token_not_valid) : errorString : getString(R.string.generic_error);
                    } catch (JSONException|NullPointerException e) {
                        e.printStackTrace();
                        errorString = null;
                    }
                    pinInfoTextView.setText(getString(R.string.generic_error));
                    Toast.makeText(getApplicationContext(), errorString == null ? getString(R.string.network_error) : errorString, Toast.LENGTH_LONG).show();
                    return;
                }
                if (response.has("days absent")) {
                    int daysAbsent = response.getInt("days absent");
                    pinCheckBox.setText(String.format(getString(R.string.missed_days_formatted), daysAbsent));
                } else {
                    pinCheckBox.setText(String.format(getString(R.string.missed_days_formatted), 0));
                }
                if (response.getBoolean("already attending")) {
                    pinInfoTextView.setText(getString(R.string.already_attending));
                    disableInput();
                    pinButton.setText(getString(R.string.enter_pin));
                } else if (response.getBoolean("pin enabled")) {
                    pinInfoTextView.setText(getString(R.string.pin_enabled));
                    enableInput();
                    pinButton.setText(R.string.enter_pin);
                } else {
                    pinInfoTextView.setText(getString(R.string.pin_disabled));
                    disableInput();
                    pinButton.setText(getString(R.string.enter_pin));
                }
            } catch (JSONException e) {
                if(MyDebug.DEBUG) Log.e(TAG, "JSON Parsing error: " + e.getMessage());
                pinInfoTextView.setText(getString(R.string.generic_error));
                disableInput();
            }
        }, error -> {
            if(MyDebug.DEBUG) Log.e(TAG, "Server Error: " + error.getMessage());

            Toast.makeText(getApplicationContext(), error.getMessage() == null || !error.getMessage().contains("UnknownHost") ? getString(R.string.generic_error) : getString(R.string.network_error), Toast.LENGTH_LONG).show();

            // stopping swipe refresh
            swipeRefreshLayout.setRefreshing(false);
            disableInput();
            pinInfoTextView.setText(getString(R.string.generic_error));
            pinArea.setVisibility(View.VISIBLE);
        });
        TalsApp.getInstance().addToRequestQueue(request);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_toolbar, menu);
        return true;
    }

    private void logout() {
        DatabaseAdapter databaseAdapter = new DatabaseAdapter(this);
        databaseAdapter.open();
        databaseAdapter.deleteEntry();
        databaseAdapter.close();
        this.startActivity(new Intent(this, LoginActivity.class));
    }

    private void help() {
        Dialog dialog = new Dialog(this);
        dialog.setTitle(R.string.menu_item_help);
        dialog.setContentView(R.layout.help_dialog);
        dialog.setCanceledOnTouchOutside(true);
        dialog.show();
    }

    private void about() {
        Dialog dialog = new Dialog(this);
        dialog.setTitle(R.string.menu_item_help);
        dialog.setContentView(R.layout.about_dialog);
        dialog.setCanceledOnTouchOutside(true);
        dialog.show();
    }

    private void exit() {
        Intent homeIntent = new Intent(Intent.ACTION_MAIN);
        homeIntent.addCategory(Intent.CATEGORY_HOME);
        homeIntent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        startActivity(homeIntent);
    }

    private void feedback() {
        Dialog dialog = new Dialog(this);
        dialog.setTitle(R.string.menu_item_feedback);
        dialog.setContentView(R.layout.feedback_dialog);
        dialog.setCanceledOnTouchOutside(true);
        dialog.show();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case R.id.menuItemAbout:
                about();
                break;
            case R.id.menuItemHelp:
                help();
                break;
            case R.id.menuItemLogout:
                logout();
                break;
            case R.id.menuItemExit:
                exit();
                break;
            case android.R.id.home:
                onBackPressed();
                break;
            case R.id.menuItemFeedback:
                feedback();
                break;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onRefresh() {
        disableInput();
        fetchPinInfo();
    }
}
