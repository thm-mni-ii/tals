package com.thm.mni.tals;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

/**
 * Created by Johannes Meintrup on 08.12.2017.
 */

public class DatabaseHelper extends SQLiteOpenHelper {

    public DatabaseHelper(Context context, String name, SQLiteDatabase.CursorFactory factory, int version) {
        super(context, name, factory, version);
    }

    @Override
    public void onCreate(SQLiteDatabase sqLiteDatabase) {
        sqLiteDatabase.execSQL(DatabaseAdapter.DATABASE_CREATE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase sqLiteDatabase, int i, int i1) {
        if(MyDebug.DEBUG) Log.w("TaskDBAdapter", "Upgrading from version " +i + " to " + i1 + ", which will destroy all old data");

        sqLiteDatabase.execSQL("DROP TABLE IF EXISTS " + "TEMPLATE");
        onCreate(sqLiteDatabase);
    }
}
