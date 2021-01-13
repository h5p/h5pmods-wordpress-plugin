/*
 * Simple sample script to track H5P scores via xAPI
 * See {@link https://h5p.org/documentation/x-api} for details on xAPI tracking
 */
(function () {
  if (!H5P || !H5P.externalDispatcher) {
    return; // Cannot track scores
  }

  /**
   * Handle xAPI statements from H5P.
   * {@link https://h5p.org/documentation/api/H5P.XAPIEvent.html}
   *
   * @param {H5P.XAPIEvent} Event containing xAPI data.
   */
  const xAPIHandler = function (event) {
    // xAPI uses JSON
    const xAPI = event.data.statement;

    if (!xAPI.result) {
      return; // No result in xAPI statement
    }

    // Output score object to console
    console.log(xAPI.result.score);
  };

  // Listen to xAPI statements
  H5P.externalDispatcher.on('xAPI', xAPIHandler);
}());
