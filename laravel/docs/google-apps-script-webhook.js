/**
 * AeroSenseV2 — Google Sheets Webhook Receiver
 * Paste this entire script into Extensions > Apps Script in your spreadsheet.
 *
 * After pasting: click "Deploy" > "New deployment" > Type: "Web app"
 *   - Execute as: Me
 *   - Who has access: Anyone
 * Copy the Web App URL and paste it into your Laravel .env as GOOGLE_SHEETS_WEBHOOK_URL
 */

function doPost(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Sheet1");
    if (!sheet) {
      sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    }

    var data = JSON.parse(e.postData.contents);

    // Ensure headers exist in row 1
    if (sheet.getLastRow() === 0) {
      sheet.appendRow(["No", "Waktu", "Fakultas", "Suhu (°C)", "Kelembapan (%)", "CO2 (ppm)", "Status"]);
    }

    var rowNum = sheet.getLastRow(); // next data row number (1-based, row 1 = header)
    var dataRowNum = rowNum; // sequential number

    // Format timestamp to Indonesian readable format
    var ts = new Date(data.timestamp);
    var formatted = Utilities.formatDate(ts, "Asia/Jakarta", "dd/MM/yyyy HH:mm:ss");

    // Append the new row
    sheet.appendRow([
      dataRowNum,       // No
      formatted,        // Waktu
      data.faculty,     // Fakultas
      data.temperature, // Suhu (°C)
      data.humidity,    // Kelembapan (%)
      data.co2,         // CO2 (ppm)
      data.status       // Status
    ]);

    Logger.log("Row added: " + JSON.stringify(data));

    return ContentService
      .createTextOutput(JSON.stringify({ success: true, row: rowNum }))
      .setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    Logger.log("Error: " + err.toString());
    return ContentService
      .createTextOutput(JSON.stringify({ success: false, error: err.toString() }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// Optional: test function to verify the script works
function testDoPost() {
  var fakeEvent = {
    postData: {
      contents: JSON.stringify({
        timestamp:   new Date().toISOString(),
        faculty:     "Fakultas Teknik",
        temperature: 28.5,
        humidity:    85.0,
        co2:         32.0,
        status:      "Baik"
      })
    }
  };
  var result = doPost(fakeEvent);
  Logger.log(result.getContent());
}
