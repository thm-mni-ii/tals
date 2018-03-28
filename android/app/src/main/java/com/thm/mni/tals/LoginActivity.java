package com.thm.mni.tals;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.StrictMode;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.CookieStore;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.params.ClientPNames;
import org.apache.http.client.protocol.ClientContext;
import org.apache.http.conn.scheme.PlainSocketFactory;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.scheme.SchemeRegistry;
import org.apache.http.conn.ssl.SSLSocketFactory;
import org.apache.http.conn.ssl.X509HostnameVerifier;
import org.apache.http.cookie.Cookie;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.conn.SingleClientConnManager;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpParams;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.apache.http.util.EntityUtils;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;

/**
 * Created by Johannes Meintrup on 08.12.2017.
 * LoginActivity class. This is the first Activity we see when we start the application.
 * Uses CasClient to authenticate with Cas.
 */
public class LoginActivity extends AppCompatActivity {
    private static final String TAG = LoginActivity.class.getSimpleName();
    private Button loginButton;
    private EditText usernameTextView;
    private EditText passwortEditText;
    private TextView errorTextView;
    private CheckBox checkBox;
    private LinearLayout loginForm;
    private ProgressBar preloadProgressBar;
    private ImageView stayLoggedInfo;
    private ImageView logo;

    public static final String TOKEN_ID = "token";
    public static final String USER_ID = "userid";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        preloadProgressBar = this.findViewById(R.id.preloadProgressBar);

        preloadProgressBar.setVisibility(View.VISIBLE);
        DatabaseAdapter databaseAdapter = new DatabaseAdapter(this);
        databaseAdapter.open();
        String token = databaseAdapter.getToken();
        String userid = databaseAdapter.getUserId();
        databaseAdapter.close();
        if (!token.equals(DatabaseAdapter.USERKEY_NOT_FOUND)) {
            Intent intent = new Intent(this, CourseListActivity.class);
            intent.putExtra(LoginActivity.TOKEN_ID, token);
            intent.putExtra(LoginActivity.USER_ID, userid);
            this.startActivity(new Intent(this, CourseListActivity.class));
        }
        preloadProgressBar.setVisibility(View.GONE);

        loginForm = this.findViewById(R.id.loginForm);
        loginForm.setVisibility(View.VISIBLE);

        loginButton = this.findViewById(R.id.loginButton);
        usernameTextView = this.findViewById(R.id.usernameTextView);
        passwortEditText = this.findViewById(R.id.passwordEditTExt);
        errorTextView = this.findViewById(R.id.errorTextView);
        checkBox = this.findViewById(R.id.saveTokenCheckBox);
        usernameTextView.setText(getString(R.string.blank));
        passwortEditText.setText(getString(R.string.blank));
        stayLoggedInfo = this.findViewById(R.id.safety_hint);
        logo = this.findViewById(R.id.logo);

        stayLoggedInfo.setOnClickListener((view) -> stayLoggedInHelp());
        loginButton.setOnClickListener(view -> onSubmit());
        passwortEditText.setOnEditorActionListener((v, actionId, event) -> {
            if(actionId == EditorInfo.IME_ACTION_DONE) {
                loginButton.performClick();
                return true;
            }
            return false;
        });
    }

    private void onSubmit() {
        String username = usernameTextView.getText().toString();
        String password = passwortEditText.getText().toString();
        boolean valid = true;
        if (username.equals("")) {
            usernameTextView.setError(getString(R.string.error_username_required));
            valid = false;
        }
        if (password.equals("")) {
            passwortEditText.setError(getString(R.string.error_password_required));
            valid = false;
        }
        if (valid) {
            errorTextView.setText(getString(R.string.blank));
            passwortEditText.setEnabled(false);
            usernameTextView.setEnabled(false);
            checkBox.setEnabled(false);
            loginButton.setEnabled(false);
            loginButton.setText(getString(R.string.currently_signing_in));
            new CasLoginTask(LoginActivity.this).execute(username, password);
        } else {
            passwortEditText.setText(getString(R.string.blank));
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_toolbar, menu);
        return true;
    }

    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        MenuItem item = menu.findItem(R.id.menuItemLogout);
        item.setEnabled(false);
        return super.onPrepareOptionsMenu(menu);
    }

    private void help() {
        Dialog dialog = new Dialog(this);
        dialog.setTitle(R.string.menu_item_help);
        dialog.setContentView(R.layout.help_dialog);
        dialog.setCanceledOnTouchOutside(true);
        dialog.show();
    }

    private void feedback() {
        Dialog dialog = new Dialog(this);
        dialog.setTitle(R.string.menu_item_feedback);
        dialog.setContentView(R.layout.feedback_dialog);
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

    private void stayLoggedInHelp() {
        AlertDialog alertDialog = new AlertDialog.Builder(this).create();
        alertDialog.setMessage(getString(R.string.stay_logged_in_help));
        alertDialog.setCanceledOnTouchOutside(false);
        alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK", (dialog, which) -> dialog.dismiss());
        alertDialog.show();
    }

    private void exit() {
        Intent homeIntent = new Intent(Intent.ACTION_MAIN);
        homeIntent.addCategory( Intent.CATEGORY_HOME );
        homeIntent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        startActivity(homeIntent);
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
            case R.id.menuItemExit:
                exit();
                break;
            case R.id.menuItemFeedback:
                feedback();
                break;
        }
        return super.onOptionsItemSelected(item);
    }


    /**
     * Disables the onBackPressed functionality of this activity
     */
    @Override
    public void onBackPressed() {
    }

    /**
     * Private Login Task extending AsyncTask.
     * This task is being executed in an extra Thread when we submit our login information.
     *
     */
    private class CasLoginTask extends AsyncTask<String, Void, CasAuthenticationResult> {
        private final String TAG = CasLoginTask.class.getSimpleName();

        private Context context;

        private CasLoginTask(Context context) {
            this.context = context;
        }

        @Override
        protected void onPreExecute() {
            passwortEditText.setEnabled(false);
            usernameTextView.setEnabled(false);
            loginButton.setEnabled(false);
            loginButton.setText(getString(R.string.currently_signing_in));
        }

        @Override
        protected CasAuthenticationResult doInBackground(String... strings) {
            final CasAuthenticationResult casAuthenticationResult = new CasAuthenticationResult();

            String username = strings[0];
            String password = strings[1];
            StrictMode.ThreadPolicy policy = new StrictMode.ThreadPolicy.Builder().permitAll().build();
            StrictMode.setThreadPolicy(policy);

            HostnameVerifier hostnameVerifier = org.apache.http.conn.ssl.SSLSocketFactory.ALLOW_ALL_HOSTNAME_VERIFIER;

            DefaultHttpClient client = new DefaultHttpClient();
            SchemeRegistry registry = new SchemeRegistry();
            SSLSocketFactory socketFactory = SSLSocketFactory.getSocketFactory();

            socketFactory.setHostnameVerifier((X509HostnameVerifier) hostnameVerifier);

            registry.register(new Scheme("https", socketFactory, 443));
            registry.register(new Scheme("http", PlainSocketFactory.getSocketFactory(), 80));
            SingleClientConnManager mgr = new SingleClientConnManager(new BasicHttpParams(), registry);
            final DefaultHttpClient httpClient = new DefaultHttpClient(mgr, new BasicHttpParams());

            HttpsURLConnection.setDefaultHostnameVerifier(hostnameVerifier);


            final CasClient casclient = new CasClient(httpClient);
            try {
                if(MyDebug.DEBUG) Log.d(TAG, "Calling the login method.");
                casclient.login("", username, password);

                CookieStore cookieStore = casclient.getCookieStore();
                HttpContext context = new BasicHttpContext();
                context.setAttribute(ClientContext.COOKIE_STORE, cookieStore);

                List<Cookie> cookies;
                cookies = cookieStore.getCookies();
                for(int i=0;i<cookies.size();i++){
                    if(MyDebug.DEBUG) Log.d(TAG,"-- Cookie: " + cookies.get(i));
                }

                HttpResponse response=null;
                HttpGet httpGet = new HttpGet (MyUrls.MOODLE_TOKEN_URL);
                boolean redirect=true;
                //Is needed to handle all the extra redirects. Hack-ish solution to this problem. There are probably some neater ways to handle it.
                while ( redirect ) {
                    redirect=false;
                    HttpParams params = httpGet.getParams();
                    params.setParameter(ClientPNames.HANDLE_REDIRECTS, Boolean.FALSE);
                    httpGet.setParams(params);

                    if(MyDebug.DEBUG) Log.d(TAG,"... working ...");
                    response = httpClient.execute(httpGet,context);

                    for(org.apache.http.Header header : response.getHeaders("Location")) {
                        if(MyDebug.DEBUG) Log.d(TAG,"Redirect Location: " + header.getValue() + "\n");
                        HttpEntity entity = response.getEntity();
                        entity.consumeContent();
                        httpGet = new HttpGet(header.getValue());
                        redirect=true;
                    }
                }
                if(MyDebug.DEBUG) Log.d(TAG, "Redirection finished!");

                HttpEntity entity = response.getEntity();

                if (entity != null) {
                    long len = entity.getContentLength();
                        JSONObject jsonObject = casclient.getTokenJSON(entity.getContent());
                        casAuthenticationResult.setToken(jsonObject.optString("token", null));
                        casAuthenticationResult.setUserId(jsonObject.optString("userid", null));
                        if(MyDebug.DEBUG) Log.d(TAG, "SET TOKEN TO " + casAuthenticationResult.getToken());
                        if(MyDebug.DEBUG) Log.d(TAG, "SET USER ID TO " + casAuthenticationResult.getUserId());
                    //}
                }
            } catch (IOException|CasProtocolException|CasAuthenticationException e) {
                if(MyDebug.DEBUG) Log.e(TAG, e.getMessage() == null ? "unknown" : e.getMessage());
                casAuthenticationResult.setException(e);
            }
            return casAuthenticationResult;
        }



        @Override
        protected void onPostExecute(CasAuthenticationResult result) {
            passwortEditText.setEnabled(true);
            usernameTextView.setEnabled(true);
            loginButton.setEnabled(true);
            checkBox.setEnabled(true);
            loginButton.setText(getString(R.string.action_sign_in));
            passwortEditText.setText(getString(R.string.blank));
            if(result.getException() == null) { //no exception found
                if(result.getToken() == null) { //no exception, but token site resulted in a wrong token.
                    if(MyDebug.DEBUG) Log.d(TAG, "Received a null token, but no exception has been added to the result!");
                    AlertDialog alertDialog = new AlertDialog.Builder(context).create();
                    alertDialog.setMessage(getString(R.string.login_failed_message));
                    alertDialog.setCanceledOnTouchOutside(false);
                    alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK", (dialog, which) -> dialog.dismiss());
                    alertDialog.show();
                } else if(result.getUserId() == null) {
                    if(MyDebug.DEBUG) Log.d(TAG, "Received token " + result.getToken() +", but no userid or exception has been added to the result!");
                    AlertDialog alertDialog = new AlertDialog.Builder(context).create();
                    alertDialog.setMessage(getString(R.string.login_failed_message));
                    alertDialog.setCanceledOnTouchOutside(false);
                    alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK", (dialog, which) -> dialog.dismiss());
                    alertDialog.show();
                }  else {
                    if(checkBox.isChecked()) { //saving the token in the database if it's there
                        DatabaseAdapter databaseAdapter = new DatabaseAdapter(context);
                        databaseAdapter.open();
                        if (!databaseAdapter.getToken().equals(DatabaseAdapter.USERKEY_NOT_FOUND)) {
                            databaseAdapter.deleteEntry();
                        }
                        databaseAdapter.insertEntry(result.getToken(), result.getUserId());
                        databaseAdapter.close();
                    }
                    Intent intent = new Intent(context, CourseListActivity.class);
                    intent.putExtra(LoginActivity.TOKEN_ID, result.getToken());
                    intent.putExtra(LoginActivity.USER_ID, result.getUserId());
                    context.startActivity(intent);
                }
            } else {
                AlertDialog alertDialog = new AlertDialog.Builder(context).create();
                Exception e = result.getException();
                alertDialog.setMessage(e instanceof CasAuthenticationException ? getString(R.string.cas_authentication_error) : e instanceof CasProtocolException ? getString(R.string.cas_protocol_error) : getString(R.string.login_failed_message));
                alertDialog.setCanceledOnTouchOutside(false);
                alertDialog.setButton(AlertDialog.BUTTON_NEUTRAL, "OK", (dialog, which) -> dialog.dismiss());
                alertDialog.show();
            }
        }
    }

}

