package com.thm.mni.tals;

import android.app.Application;
import android.support.annotation.NonNull;
import android.text.TextUtils;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.Volley;
import com.linkedin.android.shaky.EmailShakeDelegate;
import com.linkedin.android.shaky.Shaky;

/**
 * This Application was created for a THM SWT project for Dr. habil. Frank Kammer by Johannes Meintrup with the help of his project member Jonas Nimmerfroh who did alot of the work for the CAS communication.
 */


public class TalsApp extends Application {
    private Shaky shaky;

    public static final String TAG = TalsApp.class
            .getSimpleName();

    private RequestQueue mRequestQueue;

    private static TalsApp mInstance;

    @Override
    public void onCreate() {
        super.onCreate();
        this.shaky = Shaky.with(this, new LogShakeDelegateWrapper(new EmailShakeDelegate(getString(R.string.feedbackEmail))));
        shaky.startFeedbackFlow();
        mInstance = this;
    }

    public static synchronized TalsApp getInstance() {
        return mInstance;
    }

    public RequestQueue getRequestQueue() {
        if (mRequestQueue == null) {
            mRequestQueue = Volley.newRequestQueue(getApplicationContext());
        }

        return mRequestQueue;
    }

    public <T> void addToRequestQueue(Request<T> req, String tag) {
        req.setTag(TextUtils.isEmpty(tag) ? TAG : tag);
        getRequestQueue().add(req);
    }

    public <T> void addToRequestQueue(Request<T> req) {
        req.setTag(TAG);
        getRequestQueue().add(req);
    }

    public void cancelPendingRequests(Object tag) {
        if (mRequestQueue != null) {
            mRequestQueue.cancelAll(tag);
        }
    }

    @NonNull
    public Shaky getShaky() {
        return shaky;
    }
}
