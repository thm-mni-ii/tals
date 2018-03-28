package com.thm.mni.tals;


import android.app.Activity;
import android.net.Uri;
import android.util.Log;
import com.linkedin.android.shaky.Result;
import com.linkedin.android.shaky.ShakeDelegate;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;

/**
 * Based on the Example Wrapper shown in the shaky git repo.
 * Obtains the logs in addition to the screenshot.
 * Example usage:
 *      new LogShakeDelegateWrapper(new EmailShakeDelegate("hello@world.com"))
 */
public class LogShakeDelegateWrapper extends ShakeDelegate {
    private static final String TAG = LogShakeDelegateWrapper.class.getSimpleName();

    private ShakeDelegate delegate;

    /**
     * Default Constructor
     * @param delegate the delegate which handles shaky events.
     */
    public LogShakeDelegateWrapper(ShakeDelegate delegate) {
        this.delegate = delegate;
    }

    @Override
    public void collectData(Activity activity, Result data) {
        delegate.collectData(activity, data);
        File logFile = writeStringToFile(new File(activity.getFilesDir(), "logcat.txt"), getDeviceLog());
        data.getAttachments().add(Uri.fromFile(logFile));
    }

    @Override
    public void submit(Activity activity, Result result) {
        delegate.submit(activity, result);
    }

    private File writeStringToFile(File file, String contents) {
        FileOutputStream stream = null;
        try {
            stream = new FileOutputStream(file);
            stream.write(contents.getBytes());
        } catch (IOException e) {
            Log.d(TAG, "Failed to open FileOutputStream. " + e.getCause());
        } finally {
            if (stream != null) {
                try {
                    stream.close();
                } catch (IOException e) {
                    Log.d(TAG, "Failed to close stream. " + e.getCause());
                }
            }
        }
        return file;
    }

    /**
     * Requires AndroidManifest permission: android.permission.READ_LOGS
     */
    private String getDeviceLog() {
        String result = null;
        try {
            Process process = Runtime.getRuntime().exec("logcat -d");
            BufferedReader bufferedReader = new BufferedReader(
                    new InputStreamReader(process.getInputStream()));

            StringBuilder log = new StringBuilder();
            String line;
            while ((line = bufferedReader.readLine()) != null) {
                log.append(line);
            }
            result = log.toString();
        } catch (IOException e) {
            Log.d(TAG, "Failed to read device log. " + e.getCause());
        }
        return result;
    }
}