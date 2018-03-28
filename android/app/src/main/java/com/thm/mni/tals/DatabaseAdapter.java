package com.thm.mni.tals;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;

/**
 * Created by Johannes Meintrup on 08.12.2017.
 * DatabaseAdapter class. Has to be implemented to use basic Database Functionalities.
 * Taken from some basic Database Tutorials out of the android documentation.
 */
public class DatabaseAdapter {
    public static final String DATABASE_NAME = "database.db";
    public static final int DATABASE_VERSION = 1;
    public static final  String DATABASE_CREATE = "create table " + "LOGIN" + "( " + "ID" + " integer primary key autoincrement," +  "USERNAME text,USERKEY text,USERID text); ";
    private SQLiteDatabase db;
    private static final String DEFAULT_USER = "user";
    public static final String USERKEY_NOT_FOUND = "NOT EXIST";

    private final Context context;
    private DatabaseHelper dbHelper;

    protected DatabaseAdapter(Context context) {
        this.context = context;
        dbHelper = new DatabaseHelper(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    protected DatabaseAdapter open() throws SQLException {
        db = dbHelper.getWritableDatabase();
        return this;
    }

    /**
     * Closes the database.
     */
    protected void close() { db.close(); }

    /**
     * Inserts a new moodle token und userid
     * @param userKey moodle token to be saved
     * @param userId userid to be saved
     */
    protected void insertEntry(String userKey, String userId) {
        ContentValues contentValues = new ContentValues();
        contentValues.put("USERNAME", DEFAULT_USER);
        contentValues.put("USERKEY", userKey);
        contentValues.put("USERID", userId);
        db.insert("LOGIN", null, contentValues);
    }

    /**
     * Getter for the Saved Moodle Token
     * @return String of the moodle token
     */
    protected String getToken() {
        Cursor cursor = db.query("LOGIN", null, "USERNAME=?", new String[]{DEFAULT_USER},null,null,null);
        if(cursor.getCount()<1) {
            cursor.close();
            return USERKEY_NOT_FOUND;
        }
        cursor.moveToFirst();
        String userKey = cursor.getString(cursor.getColumnIndex("USERKEY"));
        cursor.close();
        return userKey;
    }

    /**
     * Getter for the moodle user id
     * @return String of the user id
     */
    protected String getUserId() {
        Cursor cursor = db.query("LOGIN", null, "USERNAME=?", new String[]{DEFAULT_USER},null,null,null);
        if(cursor.getCount()<1) {
            cursor.close();
            return USERKEY_NOT_FOUND;
        }
        cursor.moveToFirst();
        String userId = cursor.getString(cursor.getColumnIndex("USERID"));
        cursor.close();
        return userId;
    }

    /**
     * Deletes the moodle token and userid. Functions as a logout method.
     * @return idx of the entry.
     */
    protected int deleteEntry() {
        String where = "USERNAME=?";
        return db.delete("LOGIN", where, new String[]{DEFAULT_USER});
    }
}
