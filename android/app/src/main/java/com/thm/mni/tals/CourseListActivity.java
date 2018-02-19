package com.thm.mni.tals;

import android.app.Dialog;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.toolbox.JsonArrayRequest;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

/**
 * Created by Johannes Meintrup on 08.12.2017
 * Activity Class for the CourseList
 * In this Activity the user sees all his daily appointments, and can click on an appointment to switch to the PinInputActivity
 *
 */

public class CourseListActivity extends AppCompatActivity implements SwipeRefreshLayout.OnRefreshListener{

    SwipeRefreshLayout swipeRefreshLayout;
    RecyclerView recyclerView;
    private ArrayList<AppointmentData> myDataList;
    private static String TAG = CourseListActivity.class.getSimpleName();
    private CourseListAdapter courseListAdapter;
    private String token;
    private String userid;
    private Map<Integer, String> courseMap;
    //private Toolbar toolbar;


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
        homeIntent.addCategory( Intent.CATEGORY_HOME );
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
            case R.id.menuItemFeedback:
                feedback();
                break;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        token = this.getIntent().getStringExtra(LoginActivity.TOKEN_ID);
        userid = this.getIntent().getStringExtra(LoginActivity.USER_ID);
        if(token == null || userid == null) {
            DatabaseAdapter databaseAdapter = new DatabaseAdapter(this);
            databaseAdapter.open();
            token = databaseAdapter.getToken();
            userid = databaseAdapter.getUserId();
            databaseAdapter.close();
        }
        getSupportActionBar().setTitle(getString(R.string.course_list_title));

        courseMap = new HashMap<>();
        setContentView(R.layout.activity_course_list);
        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout);
        recyclerView = findViewById(R.id.recyclerView);
        Intent nextIntent = new Intent(this, PinInputActivity.class);
        nextIntent.putExtra(LoginActivity.TOKEN_ID, token);
        nextIntent.putExtra(LoginActivity.USER_ID, userid);
        LinearLayoutManager linearLayoutManager = new LinearLayoutManager(getApplicationContext());
        recyclerView.setLayoutManager(linearLayoutManager);
        myDataList = new ArrayList<>();
        courseListAdapter = new CourseListAdapter(CourseListActivity.this, myDataList, nextIntent);
        recyclerView.setAdapter(courseListAdapter);
        swipeRefreshLayout.setOnRefreshListener(this);
        swipeRefreshLayout.post(() -> {
            swipeRefreshLayout.setRefreshing(true);
            fetchAppointments();
        });
    }

    @Override
    public void onBackPressed() {

    }

    @Override
    public void onRefresh() {
        fetchAppointments();
    }

    /**
     * In this method we fetch the list of daily appointments from moodle/tals
     */
    private void fetchAppointments() {
        swipeRefreshLayout.setRefreshing(true);
        String url = MyUrls.getTodaysAppointmentRequestUrl(token, userid);
        if(MyDebug.DEBUG) Log.d(TAG, url);
        final JsonArrayRequest request = new JsonArrayRequest(url, response -> {
            if(MyDebug.DEBUG) Log.d(TAG, response.toString());
            myDataList.clear();
            if (response.length() == 0) {
                Toast.makeText(getApplicationContext(), getString(R.string.no_courses), Toast.LENGTH_LONG).show();
            }
            for (int i = 0; i < response.length(); i++) {
                try {
                    JSONObject appointment = response.getJSONObject(i);
                    int appointmentid = appointment.getInt("id");
                    String title = appointment.getString("title");
                    String type = appointment.getString("type");
                    String start = appointment.getString("start");
                    String end = appointment.getString("end");
                    int courseid = appointment.getInt("courseid");
                    boolean pinEnabled = appointment.getBoolean("pin");
                    myDataList.add(new AppointmentData(appointmentid, type, title, start, end, courseid, pinEnabled));
                } catch (JSONException e) {
                    if(MyDebug.DEBUG) Log.d(TAG, e.getMessage());
                }
            }
            /**
             * Here we fetch the courses and add the course title to our appointment data.
             * We only fetch the courses if we need them, otherwise we only add the already obtained titles in the method.
             */
            fetchCourses();
        }, error -> {
            if(MyDebug.DEBUG) Log.e(TAG, "Server Error: " + error.getMessage());
            String errorString = error.getMessage();
            try {
                errorString = errorString.substring(errorString.indexOf('{'), errorString.indexOf('}') + 1);
                if(MyDebug.DEBUG) Log.d(TAG, errorString);
                JSONObject jsonObject = new JSONObject(errorString);
                errorString = jsonObject.getString("message");
                errorString = errorString.contains("Ungültiges Token") ? getString(R.string.token_not_valid) + " " + token + " " + userid: errorString;
            } catch (JSONException e) {
                e.printStackTrace();
                errorString = getString(R.string.generic_error);
            } catch (StringIndexOutOfBoundsException|NullPointerException e) {
                e.printStackTrace();
                errorString = getString(R.string.network_error);
            }
            String errorMessage = error.getMessage() == null ? getString(R.string.network_error) : errorString;
            Toast.makeText(getApplicationContext(), errorMessage == null ? getString(R.string.generic_error) : errorMessage, Toast.LENGTH_LONG).show();
            // stopping swipe refresh
            swipeRefreshLayout.setRefreshing(false);
        });
        TalsApp.getInstance().addToRequestQueue(request);
    }

    /**
     * In this method we fetch the courselist.
     * We only fetch the course list if we don't already have it, or it does not match up with the course ids which we grab in fetchAppointments.
     */
    private void fetchCourses() {
        boolean fetch = false;
        for (AppointmentData a : myDataList) {
            if (!courseMap.containsKey(a.getCourseid())) {
                fetch = true;
                break;
            }
        }
        if(!fetch) {
            for (AppointmentData a : myDataList) {
                a.setCourseTitle(courseMap.containsKey(a.getCourseid()) ? (courseMap.get(a.getCourseid())) : getString(R.string.unknownCourse));
            }
            Collections.sort(myDataList, (a1, a2) -> (a1.getStart()+a1.getCourseTitle()+a1.getTitle()).compareTo(a2.getStart()+a2.getCourseTitle()+a2.getTitle()));
            swipeRefreshLayout.setRefreshing(false);
            courseListAdapter.notifyDataSetChanged();
        } else {
            courseMap = new HashMap<>();
            String url = MyUrls.getCourseListRequestUrl(token, userid);
            Log.d(TAG, url);
            //String url = "https://moodle.herwegh.me/webservice/rest/server.php?wstoken=" + token + "&wsfunction=mod_wstals_get_courses&userid=" + userid + "&moodlewsrestformat=json";
            final JsonArrayRequest request = new JsonArrayRequest(url, response -> {
                if(MyDebug.DEBUG) Log.d(TAG, response.toString());

                    //noCourses.setVisibility(View.VISIBLE);

                //myDataList.add(new AppointmentData(0,"asdasd", "asdasd", "asdkjalsdjk", "asd", 0, true));
                courseMap = new HashMap<>();
                for (int i = 0; i < response.length(); i++) {
                    try {
                        JSONObject course = response.getJSONObject(i);
                        String fullname = course.optString("fullname", getString(R.string.unknownCourse));
                        int courseid = course.getInt("id");
                        courseMap.put(courseid, fullname);
                    } catch (JSONException e) {
                        if(MyDebug.DEBUG) Log.d(TAG, e.getMessage());
                    }
                }
                for (AppointmentData a : myDataList) {
                    a.setCourseTitle(courseMap.containsKey(a.getCourseid()) ? (courseMap.get(a.getCourseid())) : getString(R.string.unknownCourse));
                }
                Collections.sort(myDataList, (a1, a2) -> (a1.getStart()+a1.getCourseTitle()+a1.getTitle()).compareTo(a2.getStart()+a2.getCourseTitle()+a2.getTitle()));
                swipeRefreshLayout.setRefreshing(false);
                courseListAdapter.notifyDataSetChanged();
            }, error -> {
                if(MyDebug.DEBUG) Log.e(TAG, "Server Error: " + error.getMessage());
                String errorString = error.getMessage();
                if (errorString != null) {
                    try {
                        errorString = errorString.substring(errorString.indexOf('{'), errorString.indexOf('}') + 1);
                        if(MyDebug.DEBUG) Log.d(TAG, errorString);
                        JSONObject jsonObject = new JSONObject(errorString);
                        errorString = jsonObject.getString("message");
                        errorString = errorString.contains("Ungültiges Token") ? getString(R.string.token_not_valid) + " " + token + " " + userid : errorString;
                    } catch (JSONException e) {
                        e.printStackTrace();
                        errorString = getString(R.string.generic_error);
                    } catch (IndexOutOfBoundsException | NullPointerException e) {
                        e.printStackTrace();
                        errorString = getString(R.string.network_error);
                    }
                }
                String errorMessage = error.getMessage() == null ? getString(R.string.network_error) : errorString;
                Toast.makeText(getApplicationContext(), errorMessage == null ? getString(R.string.generic_error) : errorMessage, Toast.LENGTH_LONG).show();
                // stopping swipe refresh
                swipeRefreshLayout.setRefreshing(false);
            });
            TalsApp.getInstance().addToRequestQueue(request);
        }
    }

}