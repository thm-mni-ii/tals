package com.thm.mni.tals;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import java.util.ArrayList;

/**
 * Created by Johannes Meintrup on 08.12.2017.
 * Adapter class for the RecyclerView of the CourseListActivity.
 * This class describes the way each entry in the RecyclerView is to be displayed and it's functionalities.
 */
public class CourseListAdapter extends RecyclerView.Adapter implements View.OnClickListener{
    private ArrayList<AppointmentData> dataList;
    private Context context;
    private String expandedPosition = "";
    private String userid;
    private String token;
    private Intent intent;

    public static final String APPOINTMENT_DATA = "AppointmentData";
    private static String TAG = CourseListAdapter.class.getSimpleName();

    /**
     * Constructor for the CourseListAdapter
     * @param context context of parent activity
     * @param dataList datalist which is to be updated after a swiperefresh. this adapter should be notified when it changes.
     * @param intent intent of the action when an appointment is selected. should be pininputactivity.
     */
    public CourseListAdapter(Context context, ArrayList<AppointmentData> dataList, Intent intent) {
        this.dataList = dataList;
        this.context = context;
        this.intent = intent;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.row_layout, parent, false);
        MyViewHolder vh = new MyViewHolder(v);
        vh.itemView.setOnClickListener(CourseListAdapter.this);
        vh.itemView.setTag(vh);
        return vh;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        MyViewHolder viewHolder = (MyViewHolder) holder;
        String fullName = dataList.get(position).getCourseTitle() != null ? dataList.get(position).getCourseTitle() : "Unknown";
        String title = dataList.get(position).getTitle() != null ? dataList.get(position).getTitle() : "Unbekannt";
        String type =  dataList.get(position).getType() != null ? "(" + dataList.get(position).getType() + ")" : "";
        String timeSlot = dataList.get(position).getStart() + " - " + dataList.get(position).getEnd();

        viewHolder.fullName.setText(fullName);
        viewHolder.name.setText(title + " " + type);
        viewHolder.time.setText(timeSlot);

        if (dataList.get(position).getPinEnabled()) {
            viewHolder.imageView.setImageResource(R.drawable.lock_open);
            viewHolder.imageView.setColorFilter(ContextCompat.getColor(context, R.color.primaryColor), android.graphics.PorterDuff.Mode.MULTIPLY);
        } else {
            viewHolder.imageView.setImageResource(R.drawable.lock);
            viewHolder.imageView.setColorFilter(ContextCompat.getColor(context, R.color.supportRed), android.graphics.PorterDuff.Mode.MULTIPLY);
        }
    }

    /**
     * Getter for the amount of items in the datalist.
     * @return size of datalist
     */
    @Override
    public int getItemCount() {
        return dataList.size();
    }

    @Override
    public void onClick(View v) {
        MyViewHolder viewHolder = (MyViewHolder) v.getTag();
        String data = dataList.get(viewHolder.getPosition()).getTitle();
        if(MyDebug.DEBUG) Log.d(TAG, data + "   " + dataList.get(viewHolder.getPosition()).getAppointmentid());

        intent.putExtra(APPOINTMENT_DATA, dataList.get(viewHolder.getPosition()));
        context.startActivity(intent);
    }

    /**
     * Class to describe each entry in the recyclerview.
     */
    protected static class MyViewHolder extends RecyclerView.ViewHolder {
        private TextView fullName;
        private TextView name;
        private TextView time;
        private ImageView imageView;

        protected MyViewHolder(final View itemView) {
            super(itemView);
            name = itemView.findViewById(R.id.name);
            time = itemView.findViewById(R.id.subText);
            imageView = itemView.findViewById(R.id.image);
            fullName = itemView.findViewById(R.id.fullName);
        }
    }
}