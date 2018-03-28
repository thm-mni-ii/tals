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

    public static final String TAG = TalsApp.class
            .getSimpleName();

    private RequestQueue mRequestQueue;

    private static TalsApp mInstance;

    @Override
    public void onCreate() {
        super.onCreate();
        Shaky shaky = Shaky.with(this, new LogShakeDelegateWrapper(new EmailShakeDelegate(getString(R.string.feedbackEmail))));
        shaky.startFeedbackFlow();
        mInstance = this;
    }

    /**
     * Getter for the instance of this application.
     * @return instance of the App
     */
    public static synchronized TalsApp getInstance() {
        return mInstance;
    }

    /**
     * Getter for the RequestQueue for Volley.
     * @return RequestQue for volley framework
     */
    public RequestQueue getRequestQueue() {
        if (mRequestQueue == null) {
            mRequestQueue = Volley.newRequestQueue(getApplicationContext());
        }

        return mRequestQueue;
    }

    /**
     * Add Request to Queue
     * @param req Request to be added
     * @param <T>
     */
    public <T> void addToRequestQueue(Request<T> req) {
        req.setTag(TAG);
        getRequestQueue().add(req);
    }
}
