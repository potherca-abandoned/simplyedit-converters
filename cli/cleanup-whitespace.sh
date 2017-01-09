#!/usr/bin/env bash

#/==============================================================================
#/                            Clean Up Files Whitespace
#/------------------------------------------------------------------------------
## Usage: cleanup-whitespace.sh <directory to clean>
##
## Call --help for more details
##
#/
#/ This script will convert common windows whitespace conventions to Unix style:
#/
#/ - Carriage returns will ne converted to common line-breaks
#/ - Tabs will be converted to spaces
#/
#/ Caveats:
#/ - To function this script requires dos2unix to be installed.
#/ - Only leading tabs will be converted to tabs
#/ - File extensions are used to choose which files to change. Currently the
#/   following extansions are converted: css|html|js|md|scss
#/ ------------------------------------------------------------------------------
#/ The following ExitCodes are used:
#/
#/  0  : Everything OK
#/ 64  : Undefined Error
#/
#/ 65 : Not enough parameters given
#/ 66 : The required program dos2unix is not installed
#/ 67 : The given directory does not exist
#/==============================================================================


# ==============================================================================
#                                APPLICATION VARS
# ------------------------------------------------------------------------------
# For all options see http://www.tldp.org/LDP/abs/html/options.html
set -o nounset      # Exit script on use of an undefined variable, same as "set -u"
set -o errexit      # Exit script when a command exits with non-zero status, same as "set -e"
set -o pipefail     # Makes pipeline return the exit status of the last command in the pipe that failed

declare g_sDirectoryPath=''
declare g_bShowHelp=false
declare -i g_iExitCode=0

readonly g_sScriptDirectory="$( unset CDPATH && cd $( dirname $0 ) && pwd -P )"
readonly g_sExtensions='css|html|js|md|scss'
# ==============================================================================


# ==============================================================================
#                              UTILITY FUNCTIONS
# ==============================================================================

source "${g_sScriptDirectory}/common.sh"

# ==============================================================================
#
# ------------------------------------------------------------------------------
validateDependencies() {

    local -r iResult=`command -v dos2unix > /dev/null && echo 0 || echo 1`

    if [ "${iResult}" == "1" ];then
        printError 'The program "dos2unix" is not installed. Aborting.'
        g_iExitCode=66
    fi
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
ensureNewlineAtEndOfFile() {
    local -r aFiles=("$@")
    local sFile

    printTopic 'Ensuring file ends with a line ending'

    for sFile in $aFiles;do
        printStatus "File ${sFile}"
        ex -s +"bufdo wq" "${sFile}"
    done
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
removeCarriageReturns() {
    local -r aFiles=("$@")
    local sFile

    printTopic 'Cleaning up line-endings'

    for sFile in $aFiles;do
        printStatus "File ${sFile}"
        dos2unix --keepdate --oldfile --safe --follow-symlink "${sFile}"
    done
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
replaceTabsWithSpaces() {
    local -r aFiles=("$@")
    local -r sTempFile='/tmp/expand-file'
    local sFile

    printTopic 'Replacing tabs with spaces'

    for sFile in $aFiles;do
        printStatus "File ${sFile}"
        expand --initial --tabs=4 "${sFile}" > "${sTempFile}" && mv "${sTempFile}" "${sFile}"
    done
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
finish() {
    if [ ${g_iExitCode} -eq 65 ];then
        echo ''
        usageShort "${@}"
    fi

    exit ${g_iExitCode}
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
handleParams() {

    local sParam

    for sParam in "$@";do
        if [ "${sParam}" = "--help" ];then
            g_bShowHelp=true
        fi
    done

    if [ "${g_bShowHelp}" = true ];then
        usageFull
    elif [ "$#" -ne 1 ];then
        printError 'This script expects one argument: the path to the directory to clean up.'
        g_iExitCode=65
    elif [ ! -d "$1" ];then
        printError "The given path '$1' is not a directory"
        g_iExitCode=67
    else
        g_sDirectoryPath="$1"
    fi
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
registerTraps() {
    trap finish EXIT
    trap finish ERR
}
# ==============================================================================


# ==============================================================================
#
# ------------------------------------------------------------------------------
run () {
    local -r aFiles=$(find "${g_sDirectoryPath}" -type f | grep -E "\.(${g_sExtensions})$")

    removeCarriageReturns "${aFiles[@]}"
    replaceTabsWithSpaces "${aFiles[@]}"
    ensureNewlineAtEndOfFile "${aFiles[@]}"
}
# ==============================================================================


# ==============================================================================
#                               RUN LOGIC
# ------------------------------------------------------------------------------

registerTraps

validateDependencies

handleParams "${@}"

if [ ${g_bShowHelp} == false ] && [ ${g_iExitCode} -eq 0 ];then
    run
fi

# ==============================================================================
#EOF