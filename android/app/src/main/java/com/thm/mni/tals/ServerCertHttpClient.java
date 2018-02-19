package com.thm.mni.tals;

import android.content.Context;
import android.util.Log;

import org.apache.http.conn.ClientConnectionManager;
import org.apache.http.conn.scheme.PlainSocketFactory;
import org.apache.http.conn.scheme.Scheme;
import org.apache.http.conn.scheme.SchemeRegistry;
import org.apache.http.conn.ssl.SSLSocketFactory;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.impl.conn.SingleClientConnManager;
import org.apache.http.params.BasicHttpParams;

import java.io.InputStream;
import java.security.KeyStore;

/**
 * A normal HTTP client class, except for the fact that it uses the specified BKS keystore to 
 * establish SSL connections with remote servers, instead of the standard Android keystore, which
 * not recognise all certification authorities, or self-signed certificates.
 * 
 * The BKS key store must be generated with an older version of Bouncing Castle compatible with 
 * Android.
 * 
 * @author Johannes Meintrup (adapted from the web somewhere, and the git repo linked in CasClient class)
 *
 */
public class ServerCertHttpClient extends DefaultHttpClient {
	private static final String TAG = ServerCertHttpClient.class.getSimpleName();

	private final Context activityContext;

	public ServerCertHttpClient (Context activityContext) 
	{
		// needed for embedded resource access (CAS certificate)
		this.activityContext = activityContext;
	}

	@Override protected ClientConnectionManager createClientConnectionManager() {

		SchemeRegistry registry = new SchemeRegistry();
		registry.register(new Scheme("http", PlainSocketFactory.getSocketFactory(), 80));
		registry.register(new Scheme("https", newSslSocketFactory(), 443));

		SingleClientConnManager x = new SingleClientConnManager(new BasicHttpParams(), registry);

		return x;
	}

	private SSLSocketFactory newSslSocketFactory() {


		try {
			KeyStore trusted = KeyStore.getInstance("BKS");
			InputStream in = activityContext.getResources().openRawResource(R.raw.keystore);
			try {
				trusted.load(in, "mypass".toCharArray());
					} finally {
				in.close();
					}
			return new SSLSocketFactory(trusted);
		} catch (Exception e) {
					throw new AssertionError(e);
		}
	}

}
