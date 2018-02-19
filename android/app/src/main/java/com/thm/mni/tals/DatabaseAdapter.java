package com.thm.mni.tals;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;

/**
 * Created by Johannes Meintrup on 08.12.2017.
 */
public class DatabaseAdapter {
    static final String DATABASE_NAME = "database.db";
    static final int DATABASE_VERSION = 1;
    static final  String DATABASE_CREATE = "create table " + "LOGIN" + "( " + "ID" + " integer primary key autoincrement," +  "USERNAME text,USERKEY text,USERID text); ";
    private SQLiteDatabase db;
    private static final String DEFAULT_USER = "user";
    static final String USERKEY_NOT_FOUND = "NOT EXIST";

    private final Context context;
    private DatabaseHelper dbHelper;

    DatabaseAdapter(Context context) {
        this.context = context;
        dbHelper = new DatabaseHelper(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    DatabaseAdapter open() throws SQLException {
        db = dbHelper.getWritableDatabase();
        return this;
    }

    void close() { db.close(); }

    void insertEntry(String userKey, String userId) {
        ContentValues contentValues = new ContentValues();
        contentValues.put("USERNAME", DEFAULT_USER);
        contentValues.put("USERKEY", userKey);
        contentValues.put("USERID", userId);
        db.insert("LOGIN", null, contentValues);
    }

    String getToken() {
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

    String getUserId() {
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

    int deleteEntry() {
        String where = "USERNAME=?";
        return db.delete("LOGIN", where, new String[]{DEFAULT_USER});
    }
}
