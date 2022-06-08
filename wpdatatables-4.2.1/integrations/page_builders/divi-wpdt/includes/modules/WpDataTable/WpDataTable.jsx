// External Dependencies
import React, {Component} from 'react';

// Internal Dependencies
import './style.css';

class WpDataTable extends Component {

  static slug = 'DIVI_wpDataTable';

  mounted = false;
  shortcode = '[wpdatatable]';

  constructor(props) {
    super(props);
    this.state = {
     shortcode: this.shortcode
    };
  }

  createShortCode () {
    let shortcode = '[wpdatatable'
    let tableId = this.props.id;
    let view = this.props.view;
    let var1 = this.props.var1;
    let var2 = this.props.var2;
    let var3 = this.props.var3;
    let export_file_name = this.props.export_file_name;
    let tablesCount = parseInt(this.props.table_array_length);

    if (tablesCount === 1) {
      return "Please create a wpDataTable first. You can find detailed instructions in our docs on this <a target='_blank' href='https://wpdatatables.com/documentation/general/features-overview/'>link</a>.";
    }

    if (!parseInt(tableId)) {
      return 'Please select a wpDataTable.';
    }
    shortcode += ' id=' + tableId;
    if (view) {
      shortcode += view === 'excel-like' ? ' table_view=excel' : ' table_view=regular';
    }
    if (var1) {
      shortcode += ' var1=' + var1;
    }
    if (var2) {
      shortcode += ' var2=' + var2;
    }
    if (var3) {
      shortcode += ' var3=' + var3;
    }
    if (export_file_name) {
      shortcode += ' export_file_name=' + export_file_name;
    }
    shortcode += ']'
    return shortcode

  }

  componentDidMount () {
    this.mounted = true
    if (this.mounted) {
      this.shortcode = this.createShortCode()
      this.setState({
        shortcode: this.shortcode
      })
    }
  }

  componentDidUpdate (prevProps, prevState, snapshot) {
    if (this.mounted) {
      this.shortcode = this.createShortCode()
      if (prevState.shortcode !== this.shortcode) {
        this.setState({
          shortcode: this.shortcode
        })
      }
    }
  }

  componentWillUnmount () {
    this.mounted = false
  }

  render () {
    return (
        <div dangerouslySetInnerHTML={{ __html: this.shortcode }}/>
    )
  }

}

export default WpDataTable;
