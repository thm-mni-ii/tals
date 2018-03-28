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

    /**
     * Sets the CourseTitle of this Appointment
     * @param courseTitle to be set
     */
    public void setCourseTitle(String courseTitle) {
        this.courseTitle = courseTitle;
    }

    /**
     * Gets the CourseTitle of this Appointment
     * @return courseTitle
     */
    public String getCourseTitle() {
        return courseTitle != null ? courseTitle : "";
    }

    /**
     * Gets the id of this Appointment
     * @return appointmentid
     */
    public int getAppointmentid() {
        return appointmentid;
    }

    /**
     * Gets the type of this Appointment
     * @return type or empty string if there is no type.
     */
    public String getType() {
        return type != null ? type : "";
    }

    /**
     * Gets the title of this Appointment
     * @return title or empty string if there is no title.
     */
    public String getTitle() {
        return title != null ? title : "";
    }

    /**
     * Gets the start of this Appointment
     * @return start or empty string if there is no start.
     */
    public String getStart() {
        return start != null ? start : "";
    }

    /**
     * Gets the end of this Appointment
     * @return end or empty string if there is no end.
     */
    public String getEnd() {
        return end != null ? end : "";
    }

    /**
     * Gets the course id of this Appointment
     * @return courseid.
     */
    public int getCourseid() {
        return courseid;
    }

    /**
     * Checks if the pin is enabled for this appoitnment
     * @return pinEnabled
     */
    public boolean getPinEnabled() {
        return pinEnabled;
    }

    /**
     * Constructor for the AppointmentData
     * @param appointmentid id of the appointment
     * @param type type of the appointment
     * @param title title of the appointment
     * @param start start time of the appointment (formatted string, i.e. "9:30")
     * @param end end time of the appointment (formatted string, i.e. "9:30")
     * @param courseid course id of the appointment
     * @param pinEnabled true if the pin is enabled for this appointment
     */
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
