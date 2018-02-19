package com.thm.mni.tals;

import java.io.Serializable;

/**
 * Created by Johannes Meintrup on 10.01.2018.
 * This class is used to store the Information for each Appointment
 * The Information is obtained from the TALS Plugin via Moodle WebService
 */

public class AppointmentData implements Serializable{
    private int appointmentid;
    private String type;
    private String title;
    private String start;
    private String end;
    private int courseid;
    private boolean pinEnabled;
    private String courseTitle;

    public void setCourseTitle(String courseTitle) {
        this.courseTitle = courseTitle;
    }

    public String getCourseTitle() {
        return courseTitle != null ? courseTitle : "";
    }

    public int getAppointmentid() {
        return appointmentid;
    }

    public String getType() {
        return type != null ? type : "";
    }

    public String getTitle() {
        return title != null ? title : "";
    }

    public String getStart() {
        return start != null ? start : "";
    }

    public String getEnd() {
        return end != null ? end : "";
    }

    public int getCourseid() {
        return courseid;
    }

    public boolean getPinEnabled() {
        return pinEnabled;
    }

    public AppointmentData(int appointmentid, String type, String title, String start, String end, int courseid, boolean pinEnabled) {
        this.appointmentid = appointmentid;
        this.type = type;
        this.title = title;
        this.start = start;
        this.end = end;
        this.courseid = courseid;
        this.pinEnabled = pinEnabled;
    }
}
